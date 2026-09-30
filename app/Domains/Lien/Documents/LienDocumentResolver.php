<?php

namespace App\Domains\Lien\Documents;

use App\Domains\Lien\Models\LienFiling;

/**
 * Routes a filing to its state-and-county-correct document definition.
 *
 * Fallback order: registry defaults ⊕ the seeded lien_state_rules row ⊕
 * database/data/lien_documents/{xx}.php ⊕ database/data/lien_counties/{xx}/{key}.php
 * (recording facts and notes only). A missing county file is normal; a missing
 * state file means the generic body with the seeded execution rules.
 */
class LienDocumentResolver
{
    public function resolve(LienFiling $filing, ?string $kind = null): ResolvedLienDocument
    {
        $project = $filing->project;
        $state = strtoupper((string) ($filing->jurisdiction_state ?: $project?->jobsite_state));

        if ($state === '' || ! LienDocumentRegistry::isSupported($state)) {
            throw new LienDocumentUnavailable('The filing has no jurisdiction state, so no document can be generated yet.');
        }

        $kind ??= $filing->documentType?->slug;

        if ($kind === 'demand_letter') {
            throw new LienDocumentUnavailable('Demand letters use their own generator.');
        }

        if (! in_array($kind, LienDocumentRegistry::KINDS, true)) {
            throw new LienDocumentUnavailable('This filing type has no generated document.');
        }

        $rules = LienDocumentRegistry::for($state);

        if ($rules['attorney_only']) {
            throw new LienDocumentUnavailable("{$rules['state_name']} liens are filed through an attorney; eRegister does not prepare the document.");
        }

        $entry = $rules['kinds'][$kind];

        if (! ($entry['enabled'] ?? true)) {
            throw new LienDocumentUnavailable(
                $entry['disabled_reason'] ?: "This document isn't used in {$rules['state_name']}."
            );
        }

        $countyKey = LienCountyKey::forFiling($filing);
        $county = LienDocumentRegistry::county($state, $countyKey);

        $recording = $rules['recording'];
        $notes = array_values(array_unique(array_merge(
            $rules['recording']['notes'] ?? [],
            $entry['notes'] ?? [],
        )));

        if ($county !== null) {
            $recording = array_replace($recording, $county['recording']);
            $recording['filing_office'] = array_replace(
                $rules['recording']['filing_office'],
                $county['recording']['filing_office'] ?? [],
            );
            $notes = array_values(array_unique(array_merge($notes, $county['notes'])));
        }

        $warnings = [];
        $jobsiteState = strtoupper((string) $project?->jobsite_state);

        if ($jobsiteState !== '' && $jobsiteState !== $state) {
            $warnings[] = "The filing's jurisdiction state ({$state}) differs from the jobsite state ({$jobsiteState}).";
        }

        if ($countyKey === null && LienDocumentRegistry::FAMILIES[$kind] === 'instrument') {
            $warnings[] = 'No county is set on the filing or project; the caption and filing office cannot be filled in.';
        }

        return new ResolvedLienDocument(
            state: $state,
            stateName: $rules['state_name'],
            kind: $kind,
            family: LienDocumentRegistry::FAMILIES[$kind],
            title: $entry['title'],
            statute: $entry['statute'],
            body: $entry['body'],
            templateVersion: (int) ($entry['template_version'] ?? 1),
            sections: $entry['sections'],
            clauses: $entry['clauses'],
            execution: $entry['execution'],
            service: $entry['service'],
            recording: $recording,
            attachments: array_values($entry['attachments'] ?? []),
            notes: $notes,
            countyKey: $countyKey,
            countyName: LienCountyKey::displayName(
                $filing->jurisdiction_county ?: $project?->jobsite_county ?: $project?->jobsite_county_google
            ),
            warnings: $warnings,
        );
    }
}
