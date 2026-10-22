{{-- The email as it was sent, opened from an Activity history row (ShowsSentEmails, EREG-154). --}}
<flux:modal wire:model="showEmailModal" class="w-full max-w-3xl">
    @if ($viewedEmail = $this->viewingEmail)
        <div class="space-y-4">
            <div class="pr-8">
                <flux:heading size="lg">{{ $viewedEmail->subject ?: '(no subject)' }}</flux:heading>
                <flux:text class="mt-1 text-sm">
                    To {{ $viewedEmail->recipientList() }}
                    &middot; {{ $viewedEmail->created_at->eastern()->format('M j, Y g:i A') }}
                </flux:text>
                <flux:text class="text-xs text-gray-500">
                    From {{ $viewedEmail->from_address ?: 'unknown' }}
                    @if ($viewedEmail->bcc)
                        &middot; Bcc {{ implode(', ', $viewedEmail->bcc) }}
                    @endif
                </flux:text>
                <div class="mt-2">
                    @include('admin.partials.sent-email-status', ['email' => $viewedEmail, 'withViewLink' => false])
                </div>
                @if ($viewedEmail->attachments)
                    <flux:text class="mt-1 text-xs text-gray-600">Attached: {{ implode(', ', $viewedEmail->attachments) }}</flux:text>
                @endif
            </div>

            @if ($viewedEmail->body_withheld)
                <flux:callout icon="lock-closed" color="zinc">
                    <flux:callout.text>This email holds a sign-in code or link, so its text isn't stored.</flux:callout.text>
                </flux:callout>
            @elseif ($viewerHtml = $viewedEmail->viewerHtml())
                {{-- No allow-scripts, so nothing in the stored copy runs.
                     allow-same-origin keeps the frame in the page, where it
                     paints reliably; without scripts it can't reach the page. --}}
                <iframe sandbox="allow-same-origin" referrerpolicy="no-referrer" srcdoc="{{ $viewerHtml }}" title="Email as sent"
                        class="h-[70vh] w-full rounded-lg border border-border bg-white"></iframe>
            @elseif ($viewedEmail->text_body)
                <pre class="max-h-[70vh] overflow-auto whitespace-pre-wrap rounded-lg border border-border bg-zinc-50 p-4 text-sm">{{ $viewedEmail->text_body }}</pre>
            @else
                <flux:text class="text-sm text-gray-500">No text was stored for this email.</flux:text>
            @endif
        </div>
    @endif
</flux:modal>
