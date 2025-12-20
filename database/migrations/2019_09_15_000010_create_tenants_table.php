<?php

declare(strict_types=1);

use App\Enums\TenantStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');

            $table->string('email')->unique()->nullable();
            $table->string('phone')->unique()->nullable();

            $table->string('status')->default(TenantStatusEnum::ACTIVE->value);

            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('grace_period_ends_at')->nullable();

            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();

            $table->json('data')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
}
