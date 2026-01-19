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
        Schema::create('plan_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->string('display_name')->comment('Devices Included, Storage, Sales Module, etc.');
            $table->string('feature_key')->comment('device_limit, storage_gb, module_sales, module_crm, etc.');
            $table->string('feature_type')->default('boolean')->comment('boolean, integer, string');
            $table->text('feature_value')->comment('true/false, numeric value, or string');
            $table->text('description')->nullable();
            $table->integer('display_order')->default(0);
            $table->timestamps();

            $table->unique(['plan_id', 'feature_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_features');
    }
};
