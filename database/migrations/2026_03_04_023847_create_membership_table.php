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
    {Schema::create('memberships', function (Blueprint $table) {
    $table->id();

    $table->foreignId('user_id')
          ->constrained()
          ->onDelete('cascade');

    $table->foreignId('plan_id')
          ->constrained('subscription_plans')
          ->onDelete('cascade');

    $table->enum('membership_status', ['active', 'expired', 'cancelled']);

    $table->date('start_date');
    $table->date('end_date');

    $table->boolean('auto_renew')->default(false);
    $table->dateTime('cancelled_at')->nullable();
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
      Schema::dropIfExists('memberships');
    }
};
 