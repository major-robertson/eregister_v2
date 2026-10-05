<?php

use App\Domains\Marketing\Jobs\ProcessCampaignSteps;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Lien deadline reminders - run hourly to catch different business timezones
Schedule::command('lien:send-deadline-reminders')->hourly()->withoutOverlapping();

// Lien waiver signature reminders - hourly; per-(waiver, interval) dedup log
Schedule::command('lien:send-waiver-reminders')->hourly()->withoutOverlapping();

// Marketing campaign step processing - run hourly
Schedule::job(new ProcessCampaignSteps)->hourly();

// Email sequences (abandon checkout, nurture, etc.) - run every 15 minutes
Schedule::command('email:process-sequences')->everyFifteenMinutes()->withoutOverlapping();

// Sales tax funnel report to the admins - Monday mornings Eastern (13:00 UTC)
Schedule::command('report:sales-tax-funnel --email')
    ->weeklyOn(1, '13:00')
    ->withoutOverlapping();

// Expiring resale certificates - daily digest at 9 AM Eastern (13/14 UTC)
Schedule::command('resale-cert:check-expiring --send-emails')
    ->dailyAt('13:00')
    ->withoutOverlapping();

// Google review requests, 2 days after a lien is recorded or a sales tax state
// is approved - daily at 10 AM Eastern
Schedule::command('email:send-review-requests')
    ->dailyAt('10:00')
    ->timezone('America/New_York')
    ->withoutOverlapping();

// Happy Websites intro emails and their reminders - weekdays at 10:30 AM
// Eastern. Sends nothing until HAPPY_WEBSITES_INTRO_ENABLED is on.
Schedule::command('email:send-websites-intro')
    ->weekdays()
    ->dailyAt('10:30')
    ->timezone('America/New_York')
    ->withoutOverlapping();
