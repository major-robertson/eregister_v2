<?php

namespace App\Domains\Lien\Console;

use App\Domains\Lien\Admin\Enums\KanbanColumn;
use App\Domains\Lien\Admin\Support\BoardCard;
use App\Mail\LienAgentDigest;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Emails the lien ops inbox the red cards on the lien board (BoardCard):
 * filings with no action from us for more than 2 business days, or with a
 * customer answer or email and nothing from us for a business day. Weekday
 * mornings; sends nothing when no card is red or no ops inbox is set.
 */
class SendAgentDigest extends Command
{
    protected $signature = 'lien:agent-digest
        {--dry-run : Print the list instead of emailing it}';

    protected $description = 'Email lien ops the board cards that have waited on us too long';

    public function handle(): int
    {
        $columnOrder = array_flip(array_map(fn (KanbanColumn $column) => $column->value, KanbanColumn::cases()));

        $cards = BoardCard::all()
            ->filter(fn (BoardCard $card) => $card->isRed())
            ->sortBy(fn (BoardCard $card) => $columnOrder[$card->column->value])
            ->map(fn (BoardCard $card) => [
                'business' => $card->filing->project?->business?->name ?: 'Unknown business',
                'document' => $card->filing->documentType?->name ?: 'Filing',
                'column' => $card->column->label(),
                'alert' => $card->alert,
                'url' => route('admin.liens.show', $card->filing->public_id),
            ])
            ->values()
            ->all();

        if ($cards === []) {
            $this->info('No red cards on the lien board. Nothing to send.');

            return self::SUCCESS;
        }

        $ops = config('lien.notifications.ops_email');

        if ($this->option('dry-run')) {
            foreach ($cards as $card) {
                $this->line("  {$card['business']} | {$card['document']} | {$card['column']} | {$card['alert']} | {$card['url']}");
            }

            $this->info('Dry run: '.count($cards).' red card(s) would be emailed to '.($ops ?: 'nobody (no ops inbox set)').'.');

            return self::SUCCESS;
        }

        if (blank($ops)) {
            $this->info('No lien ops inbox is set (LIEN_OPS_EMAIL). Nothing sent.');

            return self::SUCCESS;
        }

        Mail::to($ops)->queue(new LienAgentDigest($cards));

        $this->info('Emailed '.count($cards)." red card(s) to {$ops}.");

        return self::SUCCESS;
    }
}
