<?php

namespace App\Jobs;

use App\Services\RedditConversionsApi;
use DateTimeInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendRedditConversion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * @param  array<string, mixed>  $event  A fully built event from RedditConversionsApi.
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
     * Reddit only ingests conversions up to 7 days after the event
     * occurred, so retrying past that point is pointless.
     */
    public function retryUntil(): DateTimeInterface
    {
        return now()->addDays(6);
    }

    public function handle(RedditConversionsApi $api): void
    {
        if (! RedditConversionsApi::enabled()) {
            // Dispatch was gated on enabled(), so landing here means this
            // worker has stale/missing config (pre-deploy daemon). Release
            // instead of dropping; retryUntil bounds how long we try.
            Log::warning('Reddit CAPI disabled at processing time; releasing conversion job', [
                'conversion_id' => $this->event['event_metadata']['conversion_id'] ?? null,
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
