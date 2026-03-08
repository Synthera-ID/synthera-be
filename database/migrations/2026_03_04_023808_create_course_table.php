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
            
            $table->string('title', 200);
            $table->string('slug', 200)->unique();
            $table->text('description')->nullable();
            
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('content_url', 500)->nullable();
            
            $table->enum('min_tier', ['basic', 'pro', 'exclusive']);
            
            $table->boolean('is_published')->default(false);
            
            $table->timestamp('created_at')->useCurrent();

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