<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            // Set by the filing wizard at checkout (EREG-138): the date we
            // must submit the document by (the deadline minus the county's
            // recording lead, config lien.recording_lead_business_days) and
            // the business days left until then, so the board and rush fees
            // can read them without recomputing.
            $table->date('must_submit_by')->nullable()->after('document_details_json');
            $table->integer('deadline_business_days_left')->nullable()->after('must_submit_by');

            // The first time the wizard told the customer the deadline had
            // passed; dates changed after it are logged as an event.
            $table->timestamp('deadline_block_shown_at')->nullable()->after('deadline_business_days_left');
        });
    }

    public function down(): void
    {
        Schema::table('lien_filings', function (Blueprint $table) {
            $table->dropColumn(['must_submit_by', 'deadline_business_days_left', 'deadline_block_shown_at']);
        });
    }
};
