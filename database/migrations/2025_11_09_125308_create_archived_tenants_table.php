<?php

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
        Schema::create('archived_tenants', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index(); // Original tenant UUID
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('domain');
            $table->string('plan_name')->nullable();
            $table->decimal('plan_price', 10, 2)->nullable();
            $table->string('stripe_id')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->text('deletion_reason')->nullable();
            $table->json('metadata')->nullable(); // Store any additional info
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_tenants');
    }
};
