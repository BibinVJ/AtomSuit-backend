<?php

namespace App\Jobs;

use App\Enums\RolesEnum;
use App\Enums\UserStatus;
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
                'password' => $this->tenant->password ?? Hash::make('Example@123'),
                'email_verified_at' => $this->tenant->email_verified_at ?? null,
                'status' => UserStatus::ACTIVE->value,
            ]);
            $adminUser->assignRole(RolesEnum::ADMIN->value);

            if ($this->tenant->load_sample_data ?? false) {
                (new TenantSampleDataSeeder())->run();
            }
        });
    }
}
