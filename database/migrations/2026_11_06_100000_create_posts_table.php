<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('category', 16);
            $table->string('tag', 64)->nullable();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status', 16)->default('draft');
            $table->date('published_at')->nullable();
            $table->string('cover')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->json('pending')->nullable();
            $table->timestamps();

            $table->index(['status', 'category', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
