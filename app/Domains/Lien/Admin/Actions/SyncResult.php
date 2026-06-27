<?php

namespace App\Domains\Lien\Admin\Actions;

/**
 * Outcome of a {@see SyncFilingSnapshot} run: which filings on the project had
 * their fulfillment snapshot re-synced, which were skipped (and why), and any
 * already-mailed recipients whose stored address is now out of date.
 *
 * Callers use this to (a) record the blast radius on the audit event and
 * (b) tell the admin what actually happened.
 */
class SyncResult
{
    /**
     * @param  list<int>  $syncedFilingIds  filings whose snapshot was rebuilt
     * @param  list<int>  $skippedFilingIds  filings left untouched
     * @param  array<int, string>  $skippedReasons  filing id => reason ('finalized' | 'no_snapshot')
     * @param  list<string>  $staleSentRecipientWarnings  human-readable warnings
     */
    public function __construct(
        public array $syncedFilingIds = [],
        public array $skippedFilingIds = [],
        public array $skippedReasons = [],
        public array $staleSentRecipientWarnings = [],
    ) {}

    public function wasSynced(int $filingId): bool
    {
        return in_array($filingId, $this->syncedFilingIds, true);
    }

    /**
     * Why a filing was skipped, or null if it was synced.
     */
    public function skipReasonFor(int $filingId): ?string
    {
        return $this->skippedReasons[$filingId] ?? null;
    }

    /**
     * Count of *other* filings (excluding the one the admin edited) that were synced.
     */
    public function siblingSyncedCount(int $editedFilingId): int
    {
        return count(array_filter(
            $this->syncedFilingIds,
            fn (int $id): bool => $id !== $editedFilingId,
        ));
    }

    /**
     * Blast-radius metadata for the audit event payload.
     *
     * @return array{synced_filing_ids: list<int>, skipped_filing_ids: list<int>, skipped_reason: array<int, string>}
     */
    public function toAuditMeta(): array
    {
        return [
            'synced_filing_ids' => $this->syncedFilingIds,
            'skipped_filing_ids' => $this->skippedFilingIds,
            'skipped_reason' => $this->skippedReasons,
        ];
    }
}
