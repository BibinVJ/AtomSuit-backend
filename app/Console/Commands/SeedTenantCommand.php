<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:tenant
                            {--allTenants : Run the seeder for all tenants}
                            {--tenantIds=* : The IDs of the tenants to seed (comma-separated)}
                            {--tenantEmails=* : The Emails of the tenants to seed (comma-separated)} 
                            {--class= : The class name of the seeder to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run seeder for tenants';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('allTenants')) {
            $tenantIds = DB::table('tenants')->orderBy('id')->pluck('id');
        };

        if ($this->option('tenantIds')) {
            $tenantIdsSpecified = $this->option('tenantIds');

            if (is_array($tenantIdsSpecified)) {
                $tenantIdsSpecified = implode(',', $tenantIdsSpecified);
            }
            $tenantIds = array_map('trim', explode(',', $tenantIdsSpecified));
        }

        if ($this->option('tenantEmails')) {

            $tenantEmailsSpecified = $this->option('tenantEmails');

            if (is_array($tenantEmailsSpecified)) {
                $tenantEmailsSpecified = implode(',', $tenantEmailsSpecified);
            }
            $tenantEmails = array_map('trim', explode(',', $tenantEmailsSpecified));
            $tenantIds = DB::table('tenants')->whereIn('email', $tenantEmails)->orderBy('id')->pluck('id');
        }

        if (empty($tenantIds)) {
            $this->error('No tenants found.');
            return Command::FAILURE;
        }

        $seederClass = $this->option('class');

        $this->call('tenants:seed', [
            '--tenants' => $tenantIds,
            '--class' => $seederClass,
        ]);

        $this->info('Seeding completed for all tenants.');

        return Command::SUCCESS;
    }
}
