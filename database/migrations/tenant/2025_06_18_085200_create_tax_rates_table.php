<?php

use App\Enums\TaxRateTypeEnum;
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
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 10, 2);
            $table->string('type')->default(TaxRateTypeEnum::PERCENTAGE->value);
            $table->foreignId('sales_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->foreignId('purchase_account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};
