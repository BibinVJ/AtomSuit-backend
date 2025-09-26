<?php

namespace Database\Seeders;

use App\Enums\TenantStatusEnum;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plan = Plan::where('name', 'Lifetime')->first();
        
        if (!$plan) {
            $this->command->error('Lifetime plan not found. Please run PlanSeeder first.');
            return;
        }

        if (!Tenant::where('email', 'company@example.com')->exists()) {
            Tenant::create([
                'name' => 'company',
                'email' => 'company@example.com',
                'password' => Hash::make('Example@123'),
                'status' => TenantStatusEnum::ACTIVE->value,
                'trial_ends_at' => null,
                'grace_period_ends_at' => null,
                'email_verified_at' => now(),
                'plan_id' => $plan->id,
                'domain_name' => 'company',
                'load_sample_data' => true,
            ]);
        }
    }
}
