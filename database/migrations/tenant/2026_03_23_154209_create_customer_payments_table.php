<?php

use App\Enums\CustomerPaymentStatus;
use App\Enums\PaymentMethod;
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
        Schema::create('customer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->string('payment_number')->unique();
            $table->foreignId('cost_center_id')->constrained()->restrictOnDelete();
            $table->decimal('amount', 15, 4);
            $table->decimal('allocated_amount', 15, 4)->default(0);
            $table->date('payment_date');
            $table->string('payment_method')->default(PaymentMethod::CASH->value);
            $table->string('reference_number')->nullable();
            $table->string('status')->default(CustomerPaymentStatus::POSTED->value);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_payments');
    }
};
