<?php

use App\Domains\Marketing\Jobs\ProcessCampaignSteps;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Lien deadline reminders - run hourly to catch different business timezones
Schedule::command('lien:send-deadline-reminders')->hourly();

// Marketing campaign step processing - run hourly
Schedule::job(new ProcessCampaignSteps)->hourly();
