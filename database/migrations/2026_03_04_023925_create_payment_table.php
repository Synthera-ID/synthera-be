<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->enum('payment_method', ['credit_card', 'bank_transfer', 'e_wallet', 'qris']);
            $table->string("payment_code");
            $table->string('payment_gateway', 100)->default("DuitKu");
            $table->string('gateway_ref', 100)->nullable();

            $table->decimal('min_amount', 12, 2);

            $table->boolean('payment_status')->default(1);

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
        // Pastikan nama tabelnya 'payments' (jamak) sesuai dengan fungsi up
        Schema::dropIfExists('payments');
    }
};
