<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('form_application_states', function (Blueprint $table) {
            // Admin workflow status, separate from the existing customer-side
            // `status` (pending/complete) which tracks fill-out progress.
            // Defaults to 'new' so newly-paid applications show up in the
            // first kanban column without requiring an explicit transition.
            $table->string('current_admin_status')
                ->default('new')
                ->index()
                ->after('status');

            $table->timestamp('current_admin_status_changed_at')
                ->nullable()
                ->after('current_admin_status');
        });
    }

    public function down(): void
    {
        Schema::table('form_application_states', function (Blueprint $table) {
            $table->dropIndex(['current_admin_status']);
            $table->dropColumn(['current_admin_status', 'current_admin_status_changed_at']);
        });
    }
};
