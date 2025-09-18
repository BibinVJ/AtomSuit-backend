<?php

namespace Database\Seeders;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            [
                'name' => 'Expired User',
                'price' => 0,
                'interval' => 'lifetime',
                'interval_count' => 1,
                'is_trial_plan' => false,
                'trial_duration' => null,
                'is_expired_user_plan' => true,
            ],
            [
                'name' => 'Trial Plan',
                'price' => 0,
                'interval' => 'day',
                'interval_count' => 14, // trial lasts 14 days
                'is_trial_plan' => true,
                'trial_duration' => 14,
                'is_expired_user_plan' => false,
            ],
            [
                'name' => 'Standard Monthly',
                'price' => 100,
                'interval' => 'month',
                'interval_count' => 1,
                'is_trial_plan' => false,
                'trial_duration' => null,
                'is_expired_user_plan' => false,
            ],
            [
                'name' => 'Standard Yearly',
                'price' => 1000,
                'interval' => 'year',
                'interval_count' => 1,
                'is_trial_plan' => false,
                'trial_duration' => null,
                'is_expired_user_plan' => false,
            ],
            [
                'name' => 'Lifetime',
                'price' => 3000,
                'interval' => 'lifetime',
                'interval_count' => 1,
                'is_trial_plan' => false,
                'trial_duration' => null,
                'is_expired_user_plan' => false,
            ],
            
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['name' => $plan['name']], $plan);
        }
    }
}
