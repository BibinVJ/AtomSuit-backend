<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('device_key', 64)->unique();
            $table->string('device_name', 100)->nullable();
            $table->boolean('is_activated')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->enum('source', ['plan_included', 'purchased', 'admin_provisioned']);
            $table->unsignedBigInteger('provisioned_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_licenses');
    }
};
