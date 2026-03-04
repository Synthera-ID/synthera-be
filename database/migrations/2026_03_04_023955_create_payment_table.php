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
       Schema::create('payments', function (Blueprint $table) {
    $table->id();

    $table->foreignId('transaction_id')
          ->constrained()
          ->onDelete('cascade');

    $table->foreignId('user_id')
          ->constrained()
          ->onDelete('cascade');

    $table->string('payment_method', 50);
    $table->string('payment_reference', 100);

    $table->decimal('amount', 12, 2);

    $table->enum('status', ['pending', 'success', 'failed']);

    $table->timestamp('paid_at')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pastikan nama tabelnya 'payments' (jamak) sesuai dengan fungsi up
        Schema::dropIfExists('payments');
    }
};