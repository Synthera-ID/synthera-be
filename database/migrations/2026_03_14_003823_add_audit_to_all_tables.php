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
    $tables = [
        'users',
        'course_categories',
        'courses',
        'subscription_plans',
        'memberships',
        'transactions',
        'payments'
    ];

    foreach ($tables as $tbl) {
        Schema::table($tbl, function (Blueprint $table) use ($tbl) {

            if (!Schema::hasColumn($tbl,'CompanyCode')) {
                $table->string('CompanyCode',32)->nullable();
            }

            if (!Schema::hasColumn($tbl,'Status')) {
                $table->tinyInteger('Status')->default(1);
            }

            if (!Schema::hasColumn($tbl,'IsDeleted')) {
                $table->tinyInteger('IsDeleted')->default(0);
            }

            if (!Schema::hasColumn($tbl,'CreatedBy')) {
                $table->string('CreatedBy',32)->nullable();
            }

            if (!Schema::hasColumn($tbl,'CreatedDate')) {
                $table->dateTime('CreatedDate')->nullable();
            }

            if (!Schema::hasColumn($tbl,'LastUpdatedBy')) {
                $table->string('LastUpdatedBy',32)->nullable();
            }

            if (!Schema::hasColumn($tbl,'LastUpdatedDate')) {
                $table->dateTime('LastUpdatedDate')->nullable();
            }

        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    $tables = [
        'users',
        'course_categories',
        'courses',
        'subscription_plans',
        'memberships',
        'transactions',
        'payments'
    ];

    

    foreach ($tables as $tbl) {
        Schema::table($tbl, function (Blueprint $table) {

            $table->dropColumn([
                'CompanyCode',
                'Status',
                'IsDeleted',
                'CreatedBy',
                'CreatedDate',
                'LastUpdatedBy',
                'LastUpdatedDate'
            ]);

        });
    }
}
};
