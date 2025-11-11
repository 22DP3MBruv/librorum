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
        Schema::create('reading_progress', function (Blueprint $table) {
            $table->id('progress_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('book_id')->constrained('books', 'book_id')->onDelete('cascade');
            $table->integer('current_page')->default(0);
            $table->datetime('last_updated')->default(now());
            $table->enum('status', ['want_to_read', 'reading', 'completed','dropped'])->default('want_to_read');
            $table->timestamps();
            
            // Prevent duplicate entries for same user-book combination
            $table->unique(['user_id', 'book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_progress');
    }
};
