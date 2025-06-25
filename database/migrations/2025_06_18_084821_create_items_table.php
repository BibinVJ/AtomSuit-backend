<?php

use App\Enums\ItemType;
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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->foreignId('category_id')->constrained();
            $table->foreignId('unit_id')->constrained();
            $table->text('description')->nullable();
            $table->string('type')->default(ItemType::PRODUCT);
            // $table->foreignId('sales_account_id')->constrained('chart_of_accounts');
            // $table->foreignId('cogs_account_id')->constrained('chart_of_accounts');
            // $table->foreignId('inventory_account_id')->constrained('chart_of_accounts');
            // $table->foreignId('inventory_adjustment_account_id')->constrained('chart_of_accounts');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
