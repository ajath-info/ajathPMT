<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'color_theme')) {
                $table->string('color_theme', 50)->default('white')->after('cover_url');
            }
            if (!Schema::hasColumn('projects', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('color_theme');
            }
            if (!Schema::hasColumn('projects', 'client_subtitle')) {
                $table->string('client_subtitle', 255)->nullable()->after('is_pinned');
            }
            if (!Schema::hasColumn('projects', 'badge_text')) {
                $table->string('badge_text', 100)->nullable()->after('client_subtitle');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['color_theme', 'is_pinned', 'client_subtitle', 'badge_text']);
        });
    }
};

