<?php

namespace App\Domains\Business\Actions;

use App\Domains\Business\Models\Business;
use App\Domains\Business\Models\BusinessInvitation;
use App\Models\User;

/**
 * Attaches the accepting user to the invited business and selects it as
 * their current business, so a brand-new invitee lands straight on the
 * dashboard — never the create-a-business or onboarding funnels.
 */
class AcceptBusinessInvitation
{
    public function handle(User $user, BusinessInvitation $invitation): Business
    {
        abort_if($invitation->isExpired(), 410, 'This invitation has expired.');

        abort_unless(
            strcasecmp($user->email, $invitation->email) === 0,
            403,
            'This invitation was sent to a different email address.',
        );

        $business = $invitation->business;

        if (! $user->belongsToBusiness($business)) {
            $user->businesses()->attach($business->id, ['role' => $invitation->role]);
        }

        $invitation->delete();

        session(['current_business_id' => $business->id]);
        session()->forget('pending_business_invitation_id');

        // Invited users skip the onboarding wizard, which normally consumes
        // this marker — pull it so it can't fire signup pixels later if they
        // create a second business in the same session.
        session()->pull('just_registered');

        return $business;
    }
}
