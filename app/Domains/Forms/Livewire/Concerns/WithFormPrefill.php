<?php

namespace App\Domains\Forms\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Initial prefill of core data from the business profile and the
 * authenticated user.
 *
 * Only non-sensitive fields are copied. EIN is pre-filled from the
 * encrypted business column; SSN is intentionally never prefilled.
 *
 * Depends on (component-owned):
 *   - $business, $coreData
 */
trait WithFormPrefill
{
    /**
     * Pre-fill core data from the business profile.
     * Only non-sensitive fields are copied.
     */
    protected function prefillFromBusinessProfile(): void
    {
        $business = $this->business;

        // Pre-fill basic business info (fallback to name if legal_name not set)
        $legalName = $business->legal_name ?? $business->name;
        if ($legalName) {
            // Sales Tax keys the entity name as `legal_name`; LLC Formation
            // keys the same name as `llc_name`. Seed both from the business
            // name captured during account setup so each form's name field
            // pre-populates regardless of which key it uses.
            $this->coreData['legal_name'] = $legalName;
            $this->coreData['llc_name'] = $legalName;
        }
        if ($business->dba_name) {
            $this->coreData['dba_name'] = $business->dba_name;
        }
        if ($business->entity_type) {
            $this->coreData['entity_type'] = $business->entity_type;
        }
        // EIN is sensitive but persisted (encrypted at rest) so returning
        // users don't have to look it up again. SSN intentionally NOT
        // prefilled — even encrypted, surfacing it without an explicit
        // re-entry would be poor security UX.
        if ($business->fein) {
            $this->coreData['fein'] = $business->fein;
        }
        if ($business->business_address && ! $this->isEffectivelyEmpty($business->business_address)) {
            // Sales Tax keys this as `business_address`; LLC Formation keys
            // the same physical address as `principal_address`. Seed both so
            // each form's address step pre-populates from the onboarding
            // address regardless of which key it uses. Unused keys are
            // harmlessly ignored by the other form's definition.
            $this->coreData['business_address'] = $business->business_address;
            $this->coreData['principal_address'] = $business->business_address;
        }
        // Seed business_email from the signed-in user's email so the
        // contact step has a sensible default. The user can edit it
        // if the business uses a different contact address. Business
        // model has no email column today, so the authed user is the
        // canonical source.
        $user = Auth::user();
        if ($user && $user->email) {
            $this->coreData['business_email'] = $user->email;
        }
        // Only treat the profile's mailing address as real when it has
        // actual content — older drafts persisted empty composites like
        // {"zip": ""}, which must not pre-check the "different mailing
        // address" toggle on a fresh application.
        if ($business->mailing_address && ! $this->isEffectivelyEmpty($business->mailing_address)) {
            $this->coreData['mailing_address'] = $business->mailing_address;
            $this->coreData['mailing_address_same'] = '0';
        } else {
            // Default to "mailing address same as business address" so the
            // conditional mailing_address field is hidden + not required
            // unless the user explicitly toggles the switch on.
            $this->coreData['mailing_address_same'] = '1';
        }

        // Pre-fill responsible people (non-sensitive fields only). Rows
        // without at least a name are husks left by older persists — skip
        // them rather than surfacing a blank "person" in the repeater.
        $people = array_values(array_filter(
            $business->responsible_people ?? [],
            fn (array $person) => trim((string) ($person['first_name'] ?? '')) !== ''
                || trim((string) ($person['last_name'] ?? '')) !== ''
        ));
        if ($people !== []) {
            $this->coreData['responsible_people'] = $this->prepareResponsiblePeopleForForm($people);
        }
    }

    /**
     * Prepare responsible people from business profile for form use.
     * Adds UUIDs for repeater tracking.
     *
     * @param  array<int, array<string, mixed>>  $people
     * @return array<int, array<string, mixed>>
     */
    protected function prepareResponsiblePeopleForForm(array $people): array
    {
        return array_map(fn (array $person) => array_merge($person, [
            '_id' => $person['_id'] ?? Str::uuid()->toString(),
        ]), $people);
    }
}
