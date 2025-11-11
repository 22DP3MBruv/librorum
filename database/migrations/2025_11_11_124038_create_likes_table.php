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
        Schema::create('likes', function (Blueprint $table) {
            $table->id('like_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->enum('target_type', ['thread', 'comment']);
            $table->unsignedBigInteger('target_id');
            $table->datetime('created_at')->default(now());
            
            // Prevent duplicate likes from same user on same target
            $table->unique(['user_id', 'target_type', 'target_id']);
            
            // Index for polymorphic relationship lookups
            $table->index(['target_type', 'target_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
