<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-business "latest application" queries (dashboards, draft
     * lookups, webhook fallbacks) order by created_at. This index lets
     * MySQL walk the index instead of filesorting full rows — a filesort
     * over a row carrying a multi-MB definition_snapshot overflows
     * sort_buffer_size and errors (1038 "Out of sort memory").
     */
    public function up(): void
    {
        Schema::table('form_applications', function (Blueprint $table): void {
            $table->index(['business_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('form_applications', function (Blueprint $table): void {
            $table->dropIndex(['business_id', 'created_at']);
        });
    }
};
