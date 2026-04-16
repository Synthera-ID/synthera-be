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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // DATA UTAMA
            $table->string('name', 100);
            $table->string('email')->unique();
            $table->string('phone', 32)->nullable();
            $table->string('api_key', 64)->nullable();
            $table->string('password')->nullable();
            $table->string('avatar_url', 255)->nullable();
            $table->string('google_id')->unique()->nullable();
            // STATUS
            $table->boolean('is_active')->default(false);
            $table->dateTime('email_verified_at')->nullable();
            
            // 2FA FIELDS
            $table->boolean('two_factor_enabled')->default(false)->after('password');
            $table->string('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_secret');

            $table->timestamps();
            // WAJIB DARI DOSEN (AUDIT FIELD)
            $table->string('company_code', 32)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('is_deleted')->default(0);
            $table->string('created_by', 32)->nullable();
            $table->dateTime('created_date')->nullable();
            $table->string('last_updated_by', 32)->nullable();
            $table->dateTime('last_updated_date')->nullable();


            $table->rememberToken();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

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
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
