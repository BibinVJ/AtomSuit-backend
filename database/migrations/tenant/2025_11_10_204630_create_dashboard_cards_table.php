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
        Schema::create('dashboard_cards', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // total-sales, total-purchase, etc.
            $table->string('title'); // Display title
            $table->string('component'); // Frontend component name
            $table->text('description')->nullable();
            $table->string('permission')->nullable(); // Required permission to view
            $table->integer('default_width')->default(6);
            $table->integer('default_height')->default(4);
            $table->integer('default_x')->default(0); // Default X position
            $table->integer('default_y')->default(0); // Default Y position
            $table->integer('default_order')->default(0);
            $table->json('default_config')->nullable(); // Default configuration
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_cards');
    }
};
