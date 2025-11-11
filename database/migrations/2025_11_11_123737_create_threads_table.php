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
        Schema::create('threads', function (Blueprint $table) {
            $table->id('thread_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('book_id')->nullable()->constrained('books', 'book_id')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->enum('scope', ['book', 'page'])->default('book'); # Specify if thread is about the whole book or a specific page
            $table->integer('page_number')->nullable();
            $table->datetime('created_at');
            $table->datetime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('threads');
    }
};
