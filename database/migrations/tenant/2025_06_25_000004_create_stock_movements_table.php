<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('transaction_date')->default(DB::raw('CURRENT_TIMESTAMP'))->comment('Date and time of the stock transaction');

            $table->integer('quantity')->comment('Positive for incoming stock, negative for outgoing stock');
            $table->decimal('rate', 12, 2)->nullable()->comment('Unit cost or sale price (context-dependent)');
            $table->decimal('standard_cost', 12, 2)->nullable()->comment('Standard/average cost for inventory valuation');

            $table->string('source_type')->comment('Type of source, e.g., Purchase, Sale, Adjustment');
            $table->unsignedBigInteger('source_id');

            $table->string('description')->nullable()->comment('Optional description of the transaction');
            $table->string('reference')->nullable()->comment('Reference number or code for the transaction');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
