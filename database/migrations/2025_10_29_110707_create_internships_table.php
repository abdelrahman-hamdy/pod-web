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
        Schema::create('internships', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('category_id')->constrained('internship_categories')->cascadeOnDelete();
            $table->string('location');
            $table->enum('type', ['full_time', 'part_time', 'contract', 'remote', 'hybrid']);
            $table->date('application_deadline');
            $table->date('start_date');
            $table->enum('status', ['draft', 'open', 'closed'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};
