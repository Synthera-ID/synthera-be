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
        Schema::create('api_usage_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('membership_id')->constrained()->onDelete('cascade');
    $table->string('endpoint',100);
    $table->enum('method',['GET','POST','PUT','DELETE']);
    $table->smallInteger('status_code');
    $table->string('ip_address',45)->nullable();
    $table->datetime('called_at');
    $table->timestamps();
    $table->string('CompanyCode', 32)->nullable();
    $table->tinyInteger('Status')->default(1);
    $table->tinyInteger('IsDeleted')->default(0);
    $table->string('CreatedBy', 32)->nullable();
    $table->dateTime('CreatedDate')->nullable();
    $table->string('LastUpdateBy', 32)->nullable();
    $table->dateTime('LastUpdateDate')->nullable();
});
    }
  
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_usage_logs');
    }
};
