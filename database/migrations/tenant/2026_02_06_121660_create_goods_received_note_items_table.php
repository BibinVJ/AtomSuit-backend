<?php

use App\Enums\DiscountType;
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
        Schema::create('goods_received_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_received_note_id')->constrained('goods_received_notes')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained()->nullOnDelete();
            $table->json('item_meta')->nullable();
            $table->foreignId('purchase_order_item_id')->nullable()->constrained('purchase_order_items')->nullOnDelete();
            $table->string('description')->nullable();
            $table->decimal('quantity_received', 15, 4);
            $table->decimal('accepted_quantity', 15, 4);
            $table->decimal('rejected_quantity', 15, 4)->default(0);
            $table->decimal('unit_price', 15, 4)->nullable();
            $table->string('discount_type')->default(DiscountType::PERCENTAGE->value);
            $table->decimal('discount_value', 15, 4)->default(0);
            $table->decimal('discount_amount', 15, 4)->default(0);
            $table->decimal('sub_total', 15, 4)->default(0);
            $table->foreignId('tax_group_id')->nullable()->constrained()->restrictOnDelete();
            $table->json('tax_meta')->nullable();
            $table->decimal('tax_amount', 15, 4)->default(0);
            $table->decimal('total_amount', 15, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goods_received_note_items');
    }
};
