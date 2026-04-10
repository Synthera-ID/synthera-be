<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_code',50)->unique();

            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->foreignId('plan_id')
                  ->constrained('subscription_plans')
                  ->onDelete('cascade');
            
            $table->foreignId('discount_id')
                  ->nullable()
                  ->constrained('discounts')
                  ->onDelete('set null');

            $table->decimal('amount', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->decimal('final_amount', 12, 2);

            $table->enum('transaction_status', ['pending', 'paid', 'failed', 'refunded']);

            $table->text('notes')->nullable();
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

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};