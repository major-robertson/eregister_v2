<?php

namespace App\Http\Controllers;

use App\Mail\HappyWebsitesRequest;
use App\Models\SentEmail;
use App\Models\User;
use App\Support\Analytics\Gtag;
use App\Support\HappyWebsites;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * The portal's Websites page: what Happy Websites (our sister company)
 * offers, and a one-click request for a free mockup. The request emails the
 * customer's name, email, business name, city and state to their inbox, once
 * per person. Step 1 of the page's "How it works" tells the customer exactly
 * that, so keep the two in step if the email ever carries more.
 */
class HappyWebsitesController extends Controller
{
    public function show(Request $request): View
    {
        return view('portal.websites', [
            'requested' => HappyWebsites::requestedBy($request->user()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $business = $request->attributes->get('business');

        try {
            // The sent_emails row is the claim: a second click, or a second
            // tab, finds it and sends nothing.
            $claimed = SentEmail::recordOrSkip(HappyWebsites::REQUEST_EMAIL_TYPE, $user, $user, function () use ($user, $business) {
                if ($this->isTestAccount($user)) {
                    return;
                }

                Mail::to(config('happy_websites.lead_email'))->send(new HappyWebsitesRequest($user, $business));
            });
        } catch (\Throwable $e) {
            report($e);
            HappyWebsites::forgetRequest($user);

            return redirect()->route('portal.websites')
                ->with('error', __("We couldn't send your request. Please try again."));
        }

        if ($claimed) {
            Gtag::queue('websites_mockup_request');
        }

        return redirect()->route('portal.websites');
    }

    /**
     * Test accounts (test.test) get the confirmation but never reach the
     * Happy Websites inbox. The mail guard only looks at recipients, and the
     * recipient here is a real address.
     */
    private function isTestAccount(User $user): bool
    {
        $domain = Str::lower(Str::afterLast($user->email, '@'));

        return in_array($domain, config('mail.blocked_recipient_domains', []), true);
    }
}
