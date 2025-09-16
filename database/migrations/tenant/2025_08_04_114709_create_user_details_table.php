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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('alternate_email')->nullable();
            $table->string('alternate_phone')->nullable();

            $table->string('id_proof_type')->nullable()->comment('Passport, Driving License, etc.');
            $table->string('id_proof_number')->nullable();

            $table->date('dob')->nullable()->comment('Date of birth');
            $table->string('gender')->nullable()->comment('Male, Female, Other');

            $table->string('profile_image')->nullable()->comment('URL to the profile image');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
