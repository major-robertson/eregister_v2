<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lien_state_rules', function (Blueprint $table) {
            // Preliminary Notice Metadata
            $table->string('prelim_delivery_method')->default('any')->after('noi_lead_time_days');
            $table->string('prelim_recipients')->default('owner')->after('prelim_delivery_method');

            // Notice of Completion Effects
            $table->boolean('noc_shortens_deadline')->default(false)->after('prelim_recipients');
            $table->unsignedSmallInteger('lien_after_noc_days')->nullable()->after('noc_shortens_deadline');
            $table->boolean('noc_requires_prior_prelim')->default(false)->after('lien_after_noc_days');
            $table->boolean('noc_eliminates_rights_if_no_prelim')->default(false)->after('noc_requires_prior_prelim');

            // Post-Lien Notice (recipients)
            $table->string('post_lien_notice_recipients')->nullable()->after('post_lien_notice_days');

            // Enforcement calc method
            $table->string('enforcement_calc_method')->default('months_after_date')->after('post_lien_notice_recipients');

            // Owner-Occupied/Tenant restrictions
            $table->string('owner_occupied_restriction_type')->default('none')->after('owner_occupied_special_rules');
            $table->boolean('tenant_project_lien_allowed')->default(true)->after('owner_occupied_restriction_type');
            $table->string('tenant_project_restrictions')->default('none')->after('tenant_project_lien_allowed');

            // Filing Requirements
            $table->string('verification_type')->default('sworn')->after('notarization_required');
            $table->string('filing_location')->default('county_recorder')->after('efile_allowed');
            $table->text('penalty_details')->nullable()->after('wrongful_lien_penalty');

            // Lien Anchor Logic
            $table->string('lien_anchor_logic')->default('single')->after('penalty_details');
            $table->string('lien_anchor_alt_field')->nullable()->after('lien_anchor_logic');

            // Lien Rights by Claimant Type
            $table->boolean('gc_has_lien_rights')->default(true)->after('lien_anchor_alt_field');
            $table->boolean('sub_has_lien_rights')->default(true)->after('gc_has_lien_rights');
            $table->boolean('subsub_has_lien_rights')->default(true)->after('sub_has_lien_rights');
            $table->boolean('supplier_owner_has_lien_rights')->default(true)->after('subsub_has_lien_rights');
            $table->boolean('supplier_gc_has_lien_rights')->default(true)->after('supplier_owner_has_lien_rights');
            $table->boolean('supplier_sub_has_lien_rights')->default(true)->after('supplier_gc_has_lien_rights');

            // References
            $table->string('statute_url')->nullable()->after('statute_references');
            $table->text('notes')->nullable()->after('statute_url');
        });
    }

    public function down(): void
    {
        Schema::table('lien_state_rules', function (Blueprint $table) {
            $table->dropColumn([
                'prelim_delivery_method',
                'prelim_recipients',
                'noc_shortens_deadline',
                'lien_after_noc_days',
                'noc_requires_prior_prelim',
                'noc_eliminates_rights_if_no_prelim',
                'post_lien_notice_recipients',
                'enforcement_calc_method',
                'owner_occupied_restriction_type',
                'tenant_project_lien_allowed',
                'tenant_project_restrictions',
                'verification_type',
                'filing_location',
                'penalty_details',
                'lien_anchor_logic',
                'lien_anchor_alt_field',
                'gc_has_lien_rights',
                'sub_has_lien_rights',
                'subsub_has_lien_rights',
                'supplier_owner_has_lien_rights',
                'supplier_gc_has_lien_rights',
                'supplier_sub_has_lien_rights',
                'statute_url',
                'notes',
            ]);
        });
    }
};
