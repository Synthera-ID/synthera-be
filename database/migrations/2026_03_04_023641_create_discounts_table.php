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
      Schema::create('discounts', function (Blueprint $table) {
    $table->id();
    $table->string('code', 50)->unique(); // Panjang disesuaikan ERD (50)
    $table->text('description')->nullable(); 
    $table->enum('discount_type', ['percentage', 'fixed']);
    $table->decimal('discount_value', 10, 2);
    $table->decimal('min_purchase', 12, 2)->nullable();
    $table->integer('max_uses')->nullable();
    $table->date('valid_from');
    $table->date('valid_until');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
