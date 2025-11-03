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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['workshop', 'conference', 'webinar', 'networking', 'hackathon'])->default('workshop');
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->text('location')->nullable(); // Physical address or online link
            $table->integer('max_attendees')->nullable(); // null means unlimited
            $table->text('agenda')->nullable();
            $table->string('banner_image')->nullable();
            $table->datetime('registration_deadline');
            $table->datetime('chat_opens_at')->nullable();
            $table->boolean('waitlist_enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index(['start_date', 'is_active']);
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
