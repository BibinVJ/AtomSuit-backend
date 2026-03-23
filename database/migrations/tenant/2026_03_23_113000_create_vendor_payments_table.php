<?php

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
            // The chart of account asset id (e.g., Bank / Cash) used to actually pay the vendor
            $table->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();

            $table->string('payment_method')->nullable();
            $table->string('reference_number')->nullable();
            $table->decimal('amount', 15, 4);
            $table->date('payment_date');

            $table->string('status')->default('POSTED'); // e.g., POSTED, VOIDED
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
