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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('profile_visibility', ['public', 'followers', 'private'])->default('public')->after('role');
            $table->enum('reading_progress_visibility', ['public', 'followers', 'private'])->default('public')->after('profile_visibility');
            $table->enum('activity_visibility', ['public', 'followers', 'private'])->default('public')->after('reading_progress_visibility');
            $table->boolean('allow_follows')->default(true)->after('activity_visibility');
            $table->boolean('require_follow_approval')->default(false)->after('allow_follows');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'profile_visibility',
                'reading_progress_visibility',
                'activity_visibility',
                'allow_follows',
                'require_follow_approval'
            ]);
        });
    }
};
