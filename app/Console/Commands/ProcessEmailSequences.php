<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCheckoutReminder;
use App\Mail\FilingActionReminder;
use App\Models\EmailSequence;
use App\Models\SentEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessEmailSequences extends Command
{
    protected $signature = 'email:process-sequences';

    protected $description = 'Process and send due email sequences (abandon checkout, nurture, etc.)';

    public function handle(): void
    {
        $processed = 0;

        DB::transaction(function () use (&$processed) {
            $sequences = EmailSequence::query()
                ->whereNull('completed_at')
                ->whereNull('suppressed_at')
                ->where('next_send_at', '<=', now())
                ->lockForUpdate()
                ->limit(100)
                ->get();

            foreach ($sequences as $sequence) {
                try {
                    $this->processSequence($sequence);
                    $processed++;
                } catch (\Throwable $e) {
                    Log::error('ProcessEmailSequences: Failed to process sequence', [
                        'sequence_id' => $sequence->id,
                        'sequence_type' => $sequence->sequence_type,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        if ($processed > 0) {
            Log::info('ProcessEmailSequences: Processed sequences', [
                'processed' => $processed,
            ]);
        }
    }

    protected function processSequence(EmailSequence $sequence): void
    {
        $suppressionReason = $sequence->shouldSuppress();

        if ($suppressionReason) {
            $sequence->suppress($suppressionReason);

            return;
        }

        $step = $sequence->currentStep();

        if ($step === null) {
            $sequence->update(['completed_at' => now(), 'next_send_at' => null]);

            return;
        }

        $user = $sequence->user;
        $config = $sequence->config();
        $emailType = $config['email_prefix'].'_'.$step;

        SentEmail::recordOrSkip($emailType, $sequence, $user, function () use ($sequence, $step) {
            $this->dispatchMailable($sequence, $step);
        });

        $sequence->advanceStep($step);
    }

    protected function dispatchMailable(EmailSequence $sequence, int $step): void
    {
        $user = $sequence->user;

        match ($sequence->sequence_type) {
            'abandon_checkout' => Mail::to($user)->queue(new AbandonedCheckoutReminder($sequence, $step)),
            'filing_action_reminder' => Mail::to($user)->queue(new FilingActionReminder($sequence, $step)),
            default => Log::warning('ProcessEmailSequences: No mailable for sequence type', [
                'sequence_type' => $sequence->sequence_type,
            ]),
        };
    }
}
