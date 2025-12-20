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
        Schema::create('dashboard_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->foreignId('dashboard_card_id')
                ->constrained('dashboard_cards')
                ->onDelete('cascade');

            $table->string('area')->nullable()->comment('Position group, e.g. "left", "right"');
            $table->float('x')->nullable();
            $table->float('y')->nullable();
            $table->float('rotation')->default(0);
            $table->float('width')->nullable();
            $table->float('height')->nullable();
            $table->unsignedInteger('col_span')->default(0);
            $table->boolean('draggable')->default(true);
            $table->boolean('visible')->default(true);
            $table->json('config')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_layouts');
    }
};
