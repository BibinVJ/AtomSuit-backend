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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->foreignId('tax_group_id')->constrained()->restrictOnDelete();
            $table->foreignId('sales_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->foreignId('cogs_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->foreignId('inventory_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->foreignId('inventory_adjustment_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
