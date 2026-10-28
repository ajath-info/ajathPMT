<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'basecamp_id')) {
                $table->string('basecamp_id', 100)->nullable()->index()->after('id');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (!Schema::hasColumn('projects', 'basecamp_id')) {
                $table->string('basecamp_id', 100)->nullable()->index()->after('id');
            }
        });

        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'basecamp_id')) {
                $table->string('basecamp_id', 100)->nullable()->index()->after('id');
            }
        });

        Schema::table('discussions', function (Blueprint $table) {
            if (!Schema::hasColumn('discussions', 'basecamp_id')) {
                $table->string('basecamp_id', 100)->nullable()->index()->after('id');
            }
        });

        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasColumn('documents', 'basecamp_id')) {
                $table->string('basecamp_id', 100)->nullable()->index()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('basecamp_id'));
        Schema::table('projects', fn (Blueprint $table) => $table->dropColumn('basecamp_id'));
        Schema::table('tasks', fn (Blueprint $table) => $table->dropColumn('basecamp_id'));
        Schema::table('discussions', fn (Blueprint $table) => $table->dropColumn('basecamp_id'));
        Schema::table('documents', fn (Blueprint $table) => $table->dropColumn('basecamp_id'));
    }
};

