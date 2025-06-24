<?php

use App\Enums\UserStatus;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('provider')->nullable()->comment('provider name, google, linkedin');
            $table->string('provider_id')->nullable();
            $table->rememberToken();
            $table->string('status')->default(UserStatus::PENDING);
            $table->timestamp('status_updated_at')->nullable();
            $table->string('profile_image')->nullable()->comment('URL to the profile image');
            $table->string('phone')->nullable();
            $table->string('phone_verified_at')->nullable();
            $table->timestamps();
        });

        // Schema::create('reset_tokens', function (Blueprint $table) {
        //     $table->string('email')->primary();
        //     $table->string('token')->comment('token or hashed OTP');
        //     $table->timestamp('created_at')->nullable();
        // });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
