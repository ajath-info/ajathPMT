<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Task::completer() / dashboard activity feed rely on tasks.completed_by,
        // which exists in production but was never captured in a migration.
        if (!Schema::hasColumn('tasks', 'completed_by')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->unsignedBigInteger('completed_by')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('tasks', 'completed_by')) {
            Schema::table('tasks', function (Blueprint $table) {
                $table->dropIndex(['completed_by']);
                $table->dropColumn('completed_by');
            });
        }
    }
};
