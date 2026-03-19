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
            
            // Salikta primārā atslēga, lai novērstu dubultu sekošanu
            $table->primary(['follower_id', 'followee_id']);
            
            // Novērst pašsekošanu ar pārbaudes ierobežojumu (piezīme: ne visas datu bāzes to atbalsta)
            // Tiks apstrādāts arī lietojumprogrammas loġikā
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
