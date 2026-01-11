<?php

namespace App\Jobs;

use App\Models\UserLoginDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateLoginLocation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The login detail instance.
     *
     * @var \App\Models\UserLoginDetail
     */
    protected $loginDetail;

    /**
     * Create a new job instance.
     */
    public function __construct(UserLoginDetail $loginDetail)
    {
        $this->loginDetail = $loginDetail;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! $this->loginDetail->ip_address || $this->loginDetail->ip_address === '127.0.0.1') {
            return;
        }

        try {
            $response = Http::timeout(5)->get("http://ip-api.com/json/{$this->loginDetail->ip_address}");

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'success') {
                    $this->loginDetail->update([
                        'city' => $data['city'] ?? null,
                        'country' => $data['country'] ?? null,
                        'iso_code' => $data['countryCode'] ?? null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to update login location: '.$e->getMessage());
        }
    }
}
