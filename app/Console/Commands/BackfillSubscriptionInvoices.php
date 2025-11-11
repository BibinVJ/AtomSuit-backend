<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Stripe\StripeClient;

class BackfillSubscriptionInvoices extends Command
{
    protected $signature = 'subscriptions:backfill-invoices {--limit=100 : Number of invoices to fetch per subscription}';
    protected $description = 'Backfill subscription invoices from Stripe for existing subscriptions';

    protected StripeClient $stripe;

    public function handle()
    {
        if (!config('services.stripe.secret')) {
            $this->error('Stripe secret key not configured.');
            return Command::FAILURE;
        }

        $this->stripe = new StripeClient(config('services.stripe.secret'));

        // Get all subscriptions (both Stripe and manual)
        $subscriptions = Subscription::all();

        if ($subscriptions->isEmpty()) {
            $this->info('No subscriptions found.');
            return Command::SUCCESS;
        }

        $this->info("Found {$subscriptions->count()} subscriptions to process.");
        $this->newLine();

        $totalProcessed = 0;
        $totalCreated = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        $progressBar = $this->output->createProgressBar($subscriptions->count());
        $progressBar->start();

        foreach ($subscriptions as $subscription) {
            $totalProcessed++;

            try {
                // Skip manual subscriptions (offline payments)
                if (str_starts_with($subscription->stripe_id, 'manual_')) {
                    $this->createManualInvoiceIfNeeded($subscription);
                    $totalSkipped++;
                    $progressBar->advance();
                    continue;
                }

                // Fetch invoices from Stripe
                $stripeInvoices = $this->stripe->invoices->all([
                    'subscription' => $subscription->stripe_id,
                    'limit' => $this->option('limit'),
                ]);

                foreach ($stripeInvoices->data as $stripeInvoice) {
                    // Check if invoice already exists
                    $exists = SubscriptionInvoice::where('transaction_id', $stripeInvoice->id)->exists();

                    if ($exists) {
                        continue;
                    }

                    // Create invoice record
                    SubscriptionInvoice::create([
                        'subscription_id' => $subscription->id,
                        'amount' => $stripeInvoice->amount_paid / 100, // Convert from cents
                        'currency' => strtoupper($stripeInvoice->currency),
                        'payment_status' => $stripeInvoice->status === 'paid' ? 'paid' : 'failed',
                        'transaction_id' => $stripeInvoice->id,
                        'invoice_date' => Carbon::createFromTimestamp($stripeInvoice->created),
                        'metadata' => [
                            'invoice_pdf' => $stripeInvoice->invoice_pdf ?? null,
                            'hosted_invoice_url' => $stripeInvoice->hosted_invoice_url ?? null,
                            'payment_intent' => $stripeInvoice->payment_intent ?? null,
                            'number' => $stripeInvoice->number ?? null,
                        ],
                    ]);

                    $totalCreated++;
                }
            } catch (\Exception $e) {
                $totalErrors++;
                $this->newLine();
                $this->error("Error processing subscription {$subscription->stripe_id}: {$e->getMessage()}");
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✅ Backfill completed!');
        $this->newLine();
        $this->table(
            ['Metric', 'Count'],
            [
                ['Subscriptions processed', $totalProcessed],
                ['Invoices created', $totalCreated],
                ['Manual subscriptions skipped', $totalSkipped],
                ['Errors', $totalErrors],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Create a single invoice record for manual subscriptions if none exists.
     */
    protected function createManualInvoiceIfNeeded(Subscription $subscription): void
    {
        // Check if manual subscription already has an invoice
        if ($subscription->subscriptionInvoices()->exists()) {
            return;
        }

        // Create initial invoice for manual subscription
        $plan = $subscription->plan;
        if (!$plan) {
            return;
        }

        SubscriptionInvoice::create([
            'subscription_id' => $subscription->id,
            'amount' => $plan->price,
            'currency' => 'USD',
            'payment_status' => 'paid',
            'transaction_id' => 'manual_' . $subscription->id . '_initial',
            'invoice_date' => $subscription->created_at,
            'metadata' => [
                'payment_method' => 'manual',
                'note' => 'Offline payment - initial subscription',
            ],
        ]);
    }
}
