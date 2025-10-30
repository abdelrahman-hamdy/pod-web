<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // Drop URL preview-related columns
            if (Schema::hasColumn('posts', 'url')) {
                $table->dropColumn('url');
            }
            if (Schema::hasColumn('posts', 'url_title')) {
                $table->dropColumn('url_title');
            }
            if (Schema::hasColumn('posts', 'url_description')) {
                $table->dropColumn('url_description');
            }
            if (Schema::hasColumn('posts', 'url_image')) {
                $table->dropColumn('url_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('url')->nullable();
            $table->string('url_title')->nullable();
            $table->text('url_description')->nullable();
            $table->string('url_image')->nullable();
        });
    }
};


