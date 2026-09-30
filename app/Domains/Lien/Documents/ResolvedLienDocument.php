<?php

namespace App\Domains\Lien\Documents;

/**
 * The state-and-county-correct document for one filing kind: which Blade body
 * to render, what it's titled, and the recording, execution and service rules
 * that travel with it. Built by LienDocumentResolver; consumed by the
 * generators, the package list and the admin card.
 */
final class ResolvedLienDocument
{
    /**
     * @param  array<string, mixed>  $sections
     * @param  array<string, mixed>  $clauses
     * @param  array<string, mixed>  $execution
     * @param  array<string, mixed>  $service
     * @param  array<string, mixed>  $recording
     * @param  list<string>  $attachments
     * @param  list<string>  $notes
     * @param  list<string>  $warnings
     */
    public function __construct(
        public readonly string $state,
        public readonly string $stateName,
        public readonly string $kind,
        public readonly string $family,
        public readonly string $title,
        public readonly ?string $statute,
        public readonly string $body,
        public readonly int $templateVersion,
        public readonly array $sections,
        public readonly array $clauses,
        public readonly array $execution,
        public readonly array $service,
        public readonly array $recording,
        public readonly array $attachments = [],
        public readonly array $notes = [],
        public readonly ?string $countyKey = null,
        public readonly ?string $countyName = null,
        public readonly array $warnings = [],
    ) {}

    public function isInstrument(): bool
    {
        return $this->family === 'instrument';
    }

    public function isLetter(): bool
    {
        return $this->family === 'letter';
    }

    public function notaryRequired(): bool
    {
        return (bool) ($this->execution['notary'] ?? false);
    }

    public function section(string $key, mixed $default = null): mixed
    {
        return $this->sections[$key] ?? $default;
    }

    /**
     * @return list<string>
     */
    public function recipientRoles(): array
    {
        return array_values($this->service['recipients'] ?? ['owner']);
    }

    /**
     * Height of the blank recorder space on page 1, in inches, beyond the
     * page's normal top margin.
     */
    public function recorderSpaceInches(): float
    {
        $top = (float) ($this->recording['top_margin_in'] ?? 3.0);
        $other = (float) ($this->recording['other_margin_in'] ?? 1.0);

        return max(0.0, round($top - $other, 2));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'state' => $this->state,
            'state_name' => $this->stateName,
            'kind' => $this->kind,
            'family' => $this->family,
            'title' => $this->title,
            'statute' => $this->statute,
            'body' => $this->body,
            'template_version' => $this->templateVersion,
            'sections' => $this->sections,
            'clauses' => $this->clauses,
            'execution' => $this->execution,
            'service' => $this->service,
            'recording' => $this->recording,
            'attachments' => $this->attachments,
            'notes' => $this->notes,
            'county_key' => $this->countyKey,
            'county_name' => $this->countyName,
            'recorder_space_in' => $this->recorderSpaceInches(),
            'warnings' => $this->warnings,
        ];
    }
}
