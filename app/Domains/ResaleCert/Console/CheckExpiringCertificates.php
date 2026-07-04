<?php

namespace App\Domains\ResaleCert\Console;

use App\Domains\ResaleCert\Models\ResaleCertificate;
use App\Domains\ResaleCert\Models\ResaleCertificateNotification;
use App\Mail\ResaleCertificatesExpiring;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Finds resale certificates expiring within the window, records in-app
 * notifications (deduped per certificate per 7 days), and emails each
 * business a grouped digest. Runs daily via the scheduler.
 */
class CheckExpiringCertificates extends Command
{
    protected $signature = 'resale-cert:check-expiring
                            {--days=90 : Number of days to look ahead for expiring certificates}
                            {--send-emails : Send email digests to business members}';

    protected $description = 'Check for expiring resale certificates and send notifications';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $sendEmails = (bool) $this->option('send-emails');

        $expiring = ResaleCertificate::withoutGlobalScope('business')
            ->whereNotNull('expiration_date')
            ->whereBetween('expiration_date', [now()->startOfDay(), now()->addDays($days)->endOfDay()])
            ->with('business.users')
            ->get();

        if ($expiring->isEmpty()) {
            $this->info('No expiring certificates found.');

            return self::SUCCESS;
        }

        $this->info("Found {$expiring->count()} certificates expiring within {$days} days.");

        foreach ($expiring->groupBy('business_id') as $certificates) {
            $business = $certificates->first()->business;

            if (! $business) {
                continue;
            }

            $notifiable = collect();

            foreach ($certificates as $certificate) {
                $daysUntilExpiration = (int) now()->diffInDays($certificate->expiration_date);

                // One notification per certificate per 7-day window.
                $recentlyNotified = ResaleCertificateNotification::withoutGlobalScope('business')
                    ->where('resale_certificate_id', $certificate->id)
                    ->where('type', 'expiring_soon')
                    ->where('created_at', '>', now()->subDays(7))
                    ->exists();

                if ($recentlyNotified) {
                    continue;
                }

                ResaleCertificateNotification::notifyExpiring($certificate, $daysUntilExpiration);
                $notifiable->push($certificate);

                $this->line("  Certificate #{$certificate->id} ({$certificate->displayName()}) expires in {$daysUntilExpiration} days");
            }

            if (! $sendEmails || $notifiable->isEmpty()) {
                continue;
            }

            $urgent = $notifiable->filter(fn ($cert) => now()->diffInDays($cert->expiration_date) <= 30);
            $warning = $notifiable->filter(function ($cert) {
                $days = now()->diffInDays($cert->expiration_date);

                return $days > 30 && $days <= 60;
            });
            $notice = $notifiable->filter(fn ($cert) => now()->diffInDays($cert->expiration_date) > 60);

            $user = $business->users->first();

            if ($user) {
                Mail::to($user)->queue(new ResaleCertificatesExpiring($business, $urgent, $warning, $notice));
                $this->info("  Digest queued for {$user->email}");
            }
        }

        return self::SUCCESS;
    }
}
