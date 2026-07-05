<?php

namespace App\Jobs;

use App\Services\OpenAiConversionsApi;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOpenAiConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @param  array<string, mixed>  $event  A fully built event from OpenAiConversionsApi.
     */
    public function __construct(public array $event)
    {
        $this->afterCommit = true;
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300, 1800, 7200];
    }

    /**
     * OpenAI only ingests conversions with a timestamp in the last 7 days,
     * so retrying past that point is pointless.
     */
    public function retryUntil(): DateTimeInterface
    {
        return now()->addDays(6);
    }

    public function handle(OpenAiConversionsApi $api): void
    {
        if (! OpenAiConversionsApi::enabled()) {
            // Dispatch was gated on enabled(), so landing here means this
            // worker has stale/missing config (pre-deploy daemon). Release
            // instead of dropping; retryUntil bounds how long we try.
            Log::warning('OpenAI CAPI disabled at processing time; releasing conversion job', [
                'event_id' => $this->event['id'] ?? null,
            ]);

            $this->release(600);

            return;
        }

        try {
            $api->send([$this->event]);
        } catch (RequestException $e) {
            // 4xx (other than throttling) means a malformed payload or bad
            // token - retrying the same request cannot succeed, so fail
            // without burning retries. 408/429 are transient; rethrow so
            // the backoff schedule retries them.
            if ($e->response->clientError() && ! in_array($e->response->status(), [408, 429], true)) {
                $this->fail($e);

                return;
            }

            throw $e;
        }
    }
}
