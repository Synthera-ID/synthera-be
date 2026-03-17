<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
      Schema::create('subscription_plans', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->text('description');
    $table->decimal('price', 12, 2);
    $table->integer('duration_days');
    $table->enum('tier', ['basic','pro','exclusive']);
    $table->integer('max_courses')->nullable();
    $table->integer('api_daily_limit')->nullable();
    $table->integer('api_rate_limit')->nullable();
    $table->boolean('is_active')->default(true);

    $table->string('CompanyCode', 32)->nullable();
    $table->tinyInteger('Status')->default(1);
    $table->tinyInteger('IsDeleted')->default(0);
    $table->string('CreatedBy', 32)->nullable();
    $table->dateTime('CreatedDate')->nullable();
    $table->string('LastUpdateBy', 32)->nullable();
    $table->dateTime('LastUpdateDate')->nullable();
});
    }

    
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
