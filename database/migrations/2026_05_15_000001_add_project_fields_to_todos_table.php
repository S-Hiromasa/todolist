<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('todos', function (Blueprint $table): void {
            $table->foreignId('project_id')->nullable()->after('team_id')->constrained()->nullOnDelete();
            $table->foreignId('assignee_id')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('todo')->after('is_done');
        });
    }

    public function down(): void
    {
        Schema::table('todos', function (Blueprint $table): void {
            $table->dropForeign(['project_id']);
            $table->dropForeign(['assignee_id']);
            $table->dropColumn(['project_id', 'assignee_id', 'status']);
        });
    }
};
