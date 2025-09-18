<?php

namespace App\Jobs;

use App\Enums\RolesEnum;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\TenantSampleDataSeeder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Hash;

class CreateTenantAdmin implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(): void
    {
        $this->tenant->run(function () {
            $adminUser = User::create([
                'name' => $this->tenant->name,
                'email' => $this->tenant->email,
                'password' => Hash::make($this->tenant->password ?? 'Example@123'),
                'email_verified_at' => now(),
            ]);
            $adminUser->assignRole(RolesEnum::ADMIN->value);

            if ($this->tenant->load_sample_data ?? false) {
                (new TenantSampleDataSeeder())->run();
            }
        });

        if (isset($this->tenant->plan_id)) {
            $subscription = Subscription::create([
                'tenant_id' => $this->tenant->id,
                'plan_id' => $this->tenant->plan_id,
                'start_date' => now(),
                'end_date' => null,
                'is_active' => true,
            ]);
        }
    }
}
