<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            // How the document was submitted to the county recorder.
            // Distinct from the service_* namespace, which is reserved for
            // future per-party service-of-notice tracking (owner/GC/lender).
            $table->string('recording_method', 16)->nullable()->after('mailing_tracking_number');
            $table->string('recording_provider')->nullable()->after('recording_method');
            $table->string('recording_reference')->nullable()->after('recording_provider');
            $table->timestamp('recording_submitted_at')->nullable()->after('recording_reference');

            $table->index('recording_method');
        });
    }

    public function down(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            $table->dropIndex(['recording_method']);
            $table->dropColumn([
                'recording_method',
                'recording_provider',
                'recording_reference',
                'recording_submitted_at',
            ]);
        });
    }
};
