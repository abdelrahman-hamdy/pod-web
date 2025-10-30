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
        Schema::table('internship_applications', function (Blueprint $table) {
            // Drop fields we don't need
            $table->dropColumn(['graduation_year', 'gpa', 'skills', 'interests', 'portfolio_links']);

            // Add graduation status
            $table->enum('graduation_status', ['student', 'graduating_soon', 'recent_graduate', 'graduated'])->nullable()->after('major');

            // Change interests to JSON for multi-selection
            $table->json('interest_categories')->nullable()->after('experience');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_applications', function (Blueprint $table) {
            // Restore dropped columns
            $table->integer('graduation_year')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->text('skills');
            $table->text('interests');
            $table->json('portfolio_links')->nullable();

            // Drop new columns
            $table->dropColumn(['graduation_status', 'interest_categories']);
        });
    }
};
