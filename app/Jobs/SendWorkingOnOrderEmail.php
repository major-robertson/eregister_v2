<?php

namespace App\Jobs;

use App\Mail\WorkingOnOrder;
use App\Models\Payment;
use App\Models\SentEmail;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendWorkingOnOrderEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Payment $payment)
    {
        $this->afterCommit = true;
        $this->delay(static::nextAllowedSendTime());
    }

    public function handle(): void
    {
        $payment = $this->payment->fresh();

        if (! $payment || ! $payment->isPaid()) {
            return;
        }

        $user = $payment->business->users()->first();

        if (! $user) {
            return;
        }

        Mail::to($user)->send(new WorkingOnOrder($payment));

        SentEmail::markSentByType('working_on_order', $payment);
    }

    /**
     * Compute the next allowed send time within the 6am–8pm ET window.
     */
    public static function nextAllowedSendTime(): CarbonInterface
    {
        $target = now('America/New_York')->addHour();

        if ($target->hour >= 20) {
            $target = $target->addDay()->setTime(6, 0);
        } elseif ($target->hour < 6) {
            $target = $target->setTime(6, 0);
        }

        return $target->utc();
    }
}
