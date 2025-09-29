<?php

use App\Enums\PlanIntervalEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('interval')->default(PlanIntervalEnum::MONTH->value);
            $table->integer('interval_count')->default(1)->comment("e.g. 1 month, 3 months, 12 months");
            $table->boolean('is_trial_plan')->default(false);
            $table->integer('trial_duration_in_days')->nullable();
            $table->boolean('is_expired_user_plan')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
