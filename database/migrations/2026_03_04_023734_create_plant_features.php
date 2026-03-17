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
    {Schema::create('plan_features', function (Blueprint $table) {
    $table->id();
    $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
    $table->string('feature_key');
    $table->string('feature_label');
    $table->integer('limit_value')->nullable();
    $table->boolean('is_unlimited')->default(false);
    $table->text('description');
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plant_features');
    }
};
