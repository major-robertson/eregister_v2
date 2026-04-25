<?php

namespace App\Domains\Lien\Enums;

enum FilingStatus: string
{
    case Draft = 'draft';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case AwaitingClient = 'awaiting_client';
    case AwaitingEsign = 'awaiting_esign';
    case AwaitingNotary = 'awaiting_notary';
    case NeedsReview = 'needs_review';
    case ReadyToFile = 'ready_to_file';
    case WaitingOnNextStep = 'waiting_on_next_step';
    case Hold = 'hold';
    case InFulfillment = 'in_fulfillment';
    case Mailed = 'mailed';
    case SubmittedForRecording = 'submitted_for_recording';
    case Recorded = 'recorded';
    case Complete = 'complete';
    case Canceled = 'canceled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::AwaitingPayment => 'Awaiting Payment',
            self::Paid => 'Submitted',
            self::AwaitingClient => 'Awaiting Client',
            self::AwaitingEsign => 'Awaiting E-Signature',
            self::AwaitingNotary => 'Awaiting Notary',
            self::NeedsReview => 'Needs Review',
            self::ReadyToFile => 'Ready to File',
            self::WaitingOnNextStep => 'Waiting on Next Step',
            self::Hold => 'Hold',
            self::InFulfillment => 'In Fulfillment',
            self::Mailed => 'Mailed to Parties',
            self::SubmittedForRecording => 'Submitted for Recording',
            self::Recorded => 'Recorded',
            self::Complete => 'Complete',
            self::Canceled => 'Canceled',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'zinc',
            self::AwaitingPayment => 'amber',
            self::Paid => 'sky',
            self::AwaitingClient => 'orange',
            self::AwaitingEsign => 'purple',
            self::AwaitingNotary => 'violet',
            self::NeedsReview => 'amber',
            self::ReadyToFile => 'lime',
            self::WaitingOnNextStep => 'cyan',
            self::Hold => 'red',
            self::InFulfillment => 'blue',
            self::Mailed => 'indigo',
            self::SubmittedForRecording => 'teal',
            self::Recorded => 'violet',
            self::Complete => 'green',
            self::Canceled => 'red',
            self::Refunded => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'pencil',
            self::AwaitingPayment => 'credit-card',
            self::Paid => 'check',
            self::AwaitingClient => 'user',
            self::AwaitingEsign => 'pencil-square',
            self::AwaitingNotary => 'stamp',
            self::NeedsReview => 'eye',
            self::ReadyToFile => 'paper-airplane',
            self::WaitingOnNextStep => 'queue-list',
            self::Hold => 'pause-circle',
            self::InFulfillment => 'clock',
            self::Mailed => 'envelope',
            self::SubmittedForRecording => 'arrow-up-tray',
            self::Recorded => 'document-check',
            self::Complete => 'check-circle',
            self::Canceled => 'x-circle',
            self::Refunded => 'arrow-uturn-left',
        };
    }

    /**
     * Get a user-friendly description of what's happening at this status.
     */
    public function userDescription(): string
    {
        return match ($this) {
            self::Draft => 'Your filing is saved as a draft.',
            self::AwaitingPayment => 'Your filing is ready. Complete payment to proceed.',
            self::Paid => 'Payment received. Your filing is being prepared.',
            self::AwaitingClient => 'We need additional information from you to continue processing your filing.',
            self::AwaitingEsign => 'An e-signature request has been sent to you. Please check your email.',
            self::AwaitingNotary => 'Your document is awaiting notarization.',
            self::NeedsReview => 'Your response has been received and is being reviewed by our team.',
            self::ReadyToFile => 'Your filing has been reviewed and is ready to be filed.',
            self::WaitingOnNextStep => 'Your filing is waiting for a prerequisite step to be completed.',
            self::Hold => 'Your filing is on hold while an issue is being resolved.',
            self::InFulfillment => 'We are preparing and sending your notice.',
            self::Mailed => 'Your notice has been mailed to all recipients.',
            self::SubmittedForRecording => 'Your document has been submitted to the county for recording.',
            self::Recorded => 'Your document has been recorded with the county.',
            self::Complete => 'Your filing is complete. All notices have been delivered.',
            self::Canceled => 'This filing has been canceled.',
            self::Refunded => 'This filing has been refunded.',
        };
    }

    /**
     * Get the "what's next" message for the user.
     */
    public function whatsNext(): ?string
    {
        return match ($this) {
            self::Draft => 'Complete the form and submit for processing.',
            self::AwaitingPayment => 'Complete payment to begin processing.',
            self::AwaitingClient => 'Please respond to our email so we can continue processing your filing.',
            self::AwaitingEsign => 'Please complete the e-signature request sent to your email.',
            self::AwaitingNotary => 'Your document will be notarized shortly.',
            self::Paid, self::NeedsReview, self::ReadyToFile, self::WaitingOnNextStep, self::Hold, self::InFulfillment, self::Mailed, self::SubmittedForRecording, self::Recorded => 'We will email you when there is an update or if we need additional information.',
            self::Complete, self::Canceled, self::Refunded => null,
        };
    }

    /**
     * Whether the filing is waiting on the customer to take action.
     */
    public function isWaitingOnCustomer(): bool
    {
        return in_array($this, [self::AwaitingClient, self::AwaitingEsign, self::AwaitingNotary], true);
    }

    /**
     * Content context for action-reminder emails.
     *
     * @return array{headline: string, body: string, cta_label: string}
     */
    public function reminderContext(): array
    {
        return match ($this) {
            self::AwaitingClient => [
                'headline' => 'We need information from you',
                'body' => 'Your filing is on hold because we need additional information from you before we can continue processing it. Please review the request and respond at your earliest convenience so we can keep things moving.',
                'cta_label' => 'Provide Information',
            ],
            self::AwaitingEsign => [
                'headline' => 'Please sign your document',
                'body' => 'An e-signature request has been sent to you. Please check your email for the signing link and complete it so we can continue processing your filing.',
                'cta_label' => 'Sign Now',
            ],
            self::AwaitingNotary => [
                'headline' => 'Your document needs notarization',
                'body' => 'Your filing requires notarization before we can continue processing it. Please have the document notarized and send it back to us so we can move forward.',
                'cta_label' => 'View Filing Details',
            ],
            default => throw new \LogicException("No reminder context for {$this->value}"),
        };
    }

    /**
     * Check if this is a user-visible milestone status.
     */
    public function isUserMilestone(): bool
    {
        return match ($this) {
            self::Paid, self::AwaitingClient, self::AwaitingEsign, self::AwaitingNotary, self::NeedsReview, self::ReadyToFile, self::WaitingOnNextStep, self::Hold, self::InFulfillment, self::Mailed, self::SubmittedForRecording, self::Recorded, self::Complete, self::Canceled, self::Refunded => true,
            self::Draft, self::AwaitingPayment => false,
        };
    }
}
