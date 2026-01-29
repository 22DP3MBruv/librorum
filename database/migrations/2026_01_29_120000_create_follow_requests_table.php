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
        Schema::create('follow_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->foreignId('follower_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('followee_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamps();
            
            // Composite unique key to prevent duplicate requests
            $table->unique(['follower_id', 'followee_id']);
            
            // Index for faster queries
            $table->index('followee_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_requests');
    }
};
