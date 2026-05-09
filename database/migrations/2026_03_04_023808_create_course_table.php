<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // bigint primary key

            $table->unsignedBigInteger('category_id'); // foreign key

            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');

            $table->text('thumbnail_url')->nullable();
            $table->text('content_url')->nullable();
            $table->text('video_url')->nullable();

            $table->json('tag')->nullable();

            $table->enum('min_tier', ['basic', 'pro', 'exclusive']);


            $table->boolean('is_published')->default(false);

            $table->timestamps();
            $table->string('CompanyCode', 32)->nullable();
            $table->tinyInteger('Status')->default(1);
            $table->tinyInteger('IsDeleted')->default(0);
            $table->string('CreatedBy', 32)->nullable();
            $table->dateTime('CreatedDate')->nullable();
            $table->string('LastUpdateBy', 32)->nullable();
            $table->dateTime('LastUpdateDate')->nullable();

            // foreign key relation
            $table->foreign('category_id')
                ->references('id')
                ->on('course_categories')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
