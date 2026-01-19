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
            $table->string('slug')->unique()->comment('total-sales, total-purchase, etc.');
            $table->string('title');
            $table->string('component')->comment('Frontend component name');
            $table->text('description')->nullable();
            $table->string('permission')->nullable()->comment('permission name from Permission Enum');
            $table->integer('default_width')->default(6)->comment('Default width while seeding the data');
            $table->integer('default_height')->default(4)->comment('Default height while seeding the data');
            $table->integer('default_x')->default(0)->comment('Default X position while seeding the data');
            $table->integer('default_y')->default(0)->comment('Default Y position while seeding the data');
            $table->integer('default_order')->default(0);
            $table->json('default_config')->nullable();
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
