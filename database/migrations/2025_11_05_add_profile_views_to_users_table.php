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
            // Only add columns that don't exist yet
            if (!Schema::hasColumn('users', 'profile_views')) {
                $table->integer('profile_views')->default(0)->after('profile_completed');
            }
            if (!Schema::hasColumn('users', 'title')) {
                $table->string('title')->nullable()->after('bio');
            }
            if (!Schema::hasColumn('users', 'company')) {
                $table->string('company')->nullable()->after('title');
            }
            if (!Schema::hasColumn('users', 'avatar_color')) {
                $table->string('avatar_color')->nullable()->after('avatar');
            }
            if (!Schema::hasColumn('users', 'profile_onboarding_seen')) {
                $table->boolean('profile_onboarding_seen')->default(false)->after('profile_completed');
            }
            if (!Schema::hasColumn('users', 'active_status')) {
                $table->boolean('active_status')->default(false)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_views', 'title', 'company', 'avatar_color', 'profile_onboarding_seen', 'active_status']);
        });
    }
};
