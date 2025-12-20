<?php

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatusEnum;
use App\Enums\TransactionStatus;
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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->comment('the user who made the sale entry');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number');
            $table->date('sale_date');
            $table->string('status')->default(TransactionStatus::DRAFT->value);
            $table->string('payment_status')->default(PaymentStatusEnum::PENDING->value);
            $table->string('payment_method')->default(PaymentMethod::CASH->value);
            $table->text('note')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
