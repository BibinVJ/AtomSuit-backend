<?php

use App\Enums\PaymentMethod;
use App\Enums\VendorPaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('vendor_id')->constrained()->restrictOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();

            $table->foreignId('cost_center_id')->constrained()->restrictOnDelete();
            $table->string('payment_method')->default(PaymentMethod::CASH->value);
            $table->string('reference_number')->nullable();
            $table->decimal('amount', 15, 4);
            $table->date('payment_date');

            $table->string('status')->default(VendorPaymentStatus::POSTED->value);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payments');
    }
};
