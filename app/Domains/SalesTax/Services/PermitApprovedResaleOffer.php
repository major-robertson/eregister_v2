<?php

namespace App\Domains\SalesTax\Services;

use App\Domains\Forms\Models\FormApplicationState;
use App\Mail\PermitApprovedResaleOffer as PermitApprovedResaleOfferMail;
use App\Models\SentEmail;
use Illuminate\Support\Facades\Mail;

/**
 * The resale certificate offer at the moment it makes sense: a state has
 * approved the customer's sales tax registration, so they now hold the
 * permit number every resale certificate carries. One email per
 * application (the first approved state sends it), none for a business
 * that already subscribes to the generator.
 */
class PermitApprovedResaleOffer
{
    public function send(FormApplicationState $state): void
    {
        $application = $state->application;

        if (! $application || $application->form_type !== 'sales_tax_permit') {
            return;
        }

        $business = $application->business;

        if (! $business || $business->subscribed(config('resale_cert.subscription_type'))) {
            return;
        }

        $user = $application->createdBy ?? $business->users()->first();

        if (! $user) {
            return;
        }

        SentEmail::recordOrSkip('resale_offer_permit_approved', $application, $user, function () use ($application, $state, $user) {
            Mail::to($user)->queue(new PermitApprovedResaleOfferMail($application, $state->state_code, $user));
        });
    }
}
