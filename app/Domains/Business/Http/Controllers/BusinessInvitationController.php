<?php

namespace App\Domains\Business\Http\Controllers;

use App\Domains\Business\Actions\AcceptBusinessInvitation;
use App\Domains\Business\Models\BusinessInvitation;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Landing + acceptance for emailed business invitations.
 *
 * The GET is a temporary signed URL (signature expiry == invitation expiry).
 * Guests are bounced to register/login with the signed URL stashed as the
 * intended URL, so Fortify's redirect()->intended() brings them straight
 * back here once authenticated — no Fortify response classes are touched.
 * The POST is a separate CSRF-protected accept so mail-scanner GETs are
 * side-effect free; authorization is the invitation-email match, enforced
 * in the action.
 */
class BusinessInvitationController
{
    public function show(Request $request, BusinessInvitation $invitation)
    {
        if ($invitation->isExpired()) {
            return response()->view('invitations.expired', [], 410);
        }

        if (! $request->user()) {
            // Come back to this exact signed URL after register/login/2FA.
            session()->put('url.intended', $request->fullUrl());
            session()->put('pending_business_invitation_id', $invitation->id);

            $exists = User::where('email', $invitation->email)->exists();

            return redirect()
                ->route($exists ? 'login' : 'register')
                ->with('status', __("You've been invited to join :business. :action to accept.", [
                    'business' => $invitation->business->name,
                    'action' => $exists ? 'Log in' : 'Create an account',
                ]));
        }

        return view('invitations.accept', [
            'invitation' => $invitation->load('business', 'inviter'),
            'emailMatches' => strcasecmp($request->user()->email, $invitation->email) === 0,
        ]);
    }

    public function accept(Request $request, BusinessInvitation $invitation, AcceptBusinessInvitation $action)
    {
        $action->handle($request->user(), $invitation);

        return redirect()->route('dashboard');
    }
}
