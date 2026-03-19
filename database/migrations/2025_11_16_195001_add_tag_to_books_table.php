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
        // Migrācija atspējota - tiek izmantots žanra lauks
        // Schema::table('books', function (Blueprint $table) {
        //     $table->string('tag')->nullable()->after('page_count');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Taga kolonna nekad netika pievienota, tāpēc nav ko dzēst
    }
};
