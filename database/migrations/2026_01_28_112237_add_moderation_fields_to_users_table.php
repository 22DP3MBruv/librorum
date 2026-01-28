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
            $table->boolean('is_flagged')->default(false)->after('require_follow_approval');
            $table->timestamp('flagged_at')->nullable()->after('is_flagged');
            $table->text('flag_reason')->nullable()->after('flagged_at');
            $table->unsignedBigInteger('flagged_by')->nullable()->after('flag_reason');
            
            $table->foreign('flagged_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['flagged_by']);
            $table->dropColumn(['is_flagged', 'flagged_at', 'flag_reason', 'flagged_by']);
        });
    }
};
