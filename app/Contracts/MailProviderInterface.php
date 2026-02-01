<?php

namespace App\Contracts;

use App\Domains\Marketing\Services\MailResult;

interface MailProviderInterface
{
    /**
     * Send a letter.
     *
     * @param  array  $to  Recipient address
     * @param  array  $templateRef  Provider template reference (e.g., ['templateId' => 'tmpl_xxx'])
     * @param  array  $mergeVariables  Variables to merge into template
     * @param  array  $options  Mailpiece options (unused for letters, reserved for future)
     * @param  array  $metadata  Metadata to attach to the mailpiece
     * @param  string|null  $idempotencyKey  Idempotency key for retries
     */
    public function sendLetter(
        array $to,
        array $templateRef,
        array $mergeVariables,
        array $options = [],
        array $metadata = [],
        ?string $idempotencyKey = null
    ): MailResult;

    /**
     * Send a postcard.
     *
     * @param  array  $to  Recipient address
     * @param  array  $templateRef  Provider template reference (e.g., ['frontTemplateId' => '...', 'backTemplateId' => '...'])
     * @param  array  $mergeVariables  Variables to merge into template
     * @param  array  $options  Mailpiece options (e.g., ['size' => '6x4'])
     * @param  array  $metadata  Metadata to attach to the mailpiece
     * @param  string|null  $idempotencyKey  Idempotency key for retries
     */
    public function sendPostcard(
        array $to,
        array $templateRef,
        array $mergeVariables,
        array $options = [],
        array $metadata = [],
        ?string $idempotencyKey = null
    ): MailResult;
}
