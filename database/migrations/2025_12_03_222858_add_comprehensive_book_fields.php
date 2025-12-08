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
        Schema::table('books', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->string('language', 10)->default('lv');
            $table->string('publisher')->nullable();
            $table->json('subjects')->nullable(); // Array of topics/subjects
            $table->json('authors')->nullable(); // Array for multiple authors
            $table->string('isbn10')->nullable();
            $table->string('isbn13')->nullable();
            $table->date('publish_date')->nullable();
            $table->integer('publication_year')->nullable();
            $table->string('genre')->nullable();
            $table->json('external_ids')->nullable(); // Store IDs from different APIs
            $table->timestamp('last_api_sync')->nullable();
            
            // Add indexes for better performance
            $table->index(['isbn10']);
            $table->index(['isbn13']);
            $table->index(['publisher']);
            $table->index(['language']);
            $table->index(['publication_year']);
            $table->index(['genre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropIndex(['isbn10']);
            $table->dropIndex(['isbn13']);
            $table->dropIndex(['publisher']);
            $table->dropIndex(['language']);
            $table->dropIndex(['publication_year']);
            $table->dropIndex(['genre']);
            
            $table->dropColumn([
                'description', 'language', 'publisher', 'subjects', 'authors',
                'isbn10', 'isbn13', 'publish_date', 'publication_year',
                'genre', 'external_ids', 'last_api_sync'
            ]);
        });
    }
};
