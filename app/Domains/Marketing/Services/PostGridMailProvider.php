<?php

namespace App\Domains\Marketing\Services;

use App\Contracts\MailProviderInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PostGridMailProvider implements MailProviderInterface
{
    protected string $apiKey;

    protected string $baseUrl;

    protected array $fromAddress;

    protected int $maxRetries = 5;

    protected int $baseDelayMs = 1000;

    public function __construct()
    {
        $this->apiKey = config('services.postgrid.api_key');
        $this->baseUrl = config('services.postgrid.base_url', 'https://api.postgrid.com/print-mail/v1');
        $this->fromAddress = config('services.postgrid.from', []);
    }

    /**
     * Send a letter via PostGrid.
     */
    public function sendLetter(
        array $to,
        array $templateRef,
        array $mergeVariables,
        array $options = [],
        array $metadata = [],
        ?string $idempotencyKey = null
    ): MailResult {
        $templateId = $templateRef['templateId'] ?? null;

        if (! $templateId) {
            return MailResult::failure('Missing templateId in templateRef');
        }

        $payload = [
            'to' => $this->formatAddress($to),
            'from' => $this->formatAddress($this->fromAddress),
            'template' => $templateId,
            'mergeVariables' => $mergeVariables,
            'mailingClass' => $options['mailingClass'] ?? 'standard_class',
            'color' => $options['color'] ?? false, // Black and white by default
        ];

        if (! empty($metadata)) {
            $payload['metadata'] = $metadata;
        }

        return $this->sendWithRetry('letters', $payload, $idempotencyKey);
    }

    /**
     * Send a postcard via PostGrid.
     */
    public function sendPostcard(
        array $to,
        array $templateRef,
        array $mergeVariables,
        array $options = [],
        array $metadata = [],
        ?string $idempotencyKey = null
    ): MailResult {
        $frontTemplateId = $templateRef['frontTemplateId'] ?? null;
        $backTemplateId = $templateRef['backTemplateId'] ?? null;

        if (! $frontTemplateId || ! $backTemplateId) {
            return MailResult::failure('Missing frontTemplateId or backTemplateId in templateRef');
        }

        $payload = [
            'to' => $this->formatAddress($to),
            'from' => $this->formatAddress($this->fromAddress),
            'frontTemplate' => $frontTemplateId,
            'backTemplate' => $backTemplateId,
            'mergeVariables' => $mergeVariables,
            'mailingClass' => $options['mailingClass'] ?? 'standard_class',
        ];

        // Add size if specified
        if (! empty($options['size'])) {
            $payload['size'] = $options['size'];
        }

        if (! empty($metadata)) {
            $payload['metadata'] = $metadata;
        }

        return $this->sendWithRetry('postcards', $payload, $idempotencyKey);
    }

    /**
     * Send a request with retry logic.
     */
    protected function sendWithRetry(string $endpoint, array $payload, ?string $idempotencyKey): MailResult
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $this->maxRetries) {
            try {
                $response = $this->makeRequest($endpoint, $payload, $idempotencyKey);

                if ($response->successful()) {
                    $data = $response->json();

                    return MailResult::success(
                        providerId: $data['id'] ?? '',
                        status: $data['status'] ?? 'ready',
                        payload: $data,
                    );
                }

                $statusCode = $response->status();

                // Retry on 429 (rate limit) and 503 (service unavailable)
                if (in_array($statusCode, [429, 503])) {
                    $lastError = "HTTP {$statusCode}: ".$response->body();
                    $attempt++;
                    $this->wait($attempt);

                    continue;
                }

                // Non-retryable error
                return MailResult::failure(
                    errorMessage: "HTTP {$statusCode}: ".$response->body(),
                    retryable: false,
                    payload: $response->json(),
                );
            } catch (ConnectionException $e) {
                // Network error - retry
                $lastError = 'Connection error: '.$e->getMessage();
                $attempt++;
                $this->wait($attempt);

                continue;
            } catch (RequestException $e) {
                // Request error - check if retryable
                $statusCode = $e->response?->status() ?? 500;

                if (in_array($statusCode, [429, 503])) {
                    $lastError = "HTTP {$statusCode}: ".$e->getMessage();
                    $attempt++;
                    $this->wait($attempt);

                    continue;
                }

                return MailResult::failure(
                    errorMessage: $e->getMessage(),
                    retryable: false,
                    payload: $e->response?->json(),
                );
            } catch (\Throwable $e) {
                Log::error('PostGrid API error', [
                    'endpoint' => $endpoint,
                    'error' => $e->getMessage(),
                ]);

                return MailResult::failure(
                    errorMessage: $e->getMessage(),
                    retryable: false,
                );
            }
        }

        // All retries exhausted
        return MailResult::failure(
            errorMessage: "Max retries exceeded. Last error: {$lastError}",
            retryable: true,
        );
    }

    /**
     * Make the HTTP request.
     */
    protected function makeRequest(string $endpoint, array $payload, ?string $idempotencyKey)
    {
        $headers = [
            'x-api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
        ];

        if ($idempotencyKey) {
            $headers['Idempotency-Key'] = $idempotencyKey;
        }

        return Http::withHeaders($headers)
            ->timeout(30)
            ->post("{$this->baseUrl}/{$endpoint}", $payload);
    }

    /**
     * Wait with exponential backoff and jitter.
     */
    protected function wait(int $attempt): void
    {
        // Exponential backoff: 1s, 2s, 4s, 8s, 16s
        $delay = $this->baseDelayMs * (2 ** ($attempt - 1));

        // Add jitter (0-25% of delay)
        $jitter = random_int(0, (int) ($delay * 0.25));
        $totalDelay = $delay + $jitter;

        // Cap at 30 seconds
        $totalDelay = min($totalDelay, 30000);

        usleep($totalDelay * 1000);
    }

    /**
     * Format an address array for PostGrid.
     */
    protected function formatAddress(array $address): array
    {
        return [
            'firstName' => $address['first_name'] ?? $address['firstName'] ?? '',
            'lastName' => $address['last_name'] ?? $address['lastName'] ?? '',
            'companyName' => $address['company_name'] ?? $address['companyName'] ?? '',
            'addressLine1' => $address['address_line_1'] ?? $address['addressLine1'] ?? $address['address'] ?? '',
            'addressLine2' => $address['address_line_2'] ?? $address['addressLine2'] ?? $address['address_2'] ?? '',
            'city' => $address['city'] ?? '',
            'provinceOrState' => $address['state'] ?? $address['provinceOrState'] ?? '',
            'postalOrZip' => $address['zip'] ?? $address['postal_code'] ?? $address['postalOrZip'] ?? '',
            'country' => $address['country'] ?? 'US',
        ];
    }
}
