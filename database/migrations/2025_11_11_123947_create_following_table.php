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
        Schema::create('following', function (Blueprint $table) {
            $table->foreignId('follower_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->foreignId('followee_id')->constrained('users', 'user_id')->onDelete('cascade');
            $table->datetime('created_at')->default(now());
            
            // Composite primary key to prevent duplicate follows
            $table->primary(['follower_id', 'followee_id']);
            
            // Prevent self-following with check constraint (note: not all databases support this)
            // Will be handled in application logic as well
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('following');
    }
};
