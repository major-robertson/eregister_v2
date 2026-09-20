<?php

namespace App\Http\Controllers;

use App\Domains\Lien\Enums\WaiverDirection;
use App\Domains\Lien\Enums\WaiverKind;
use App\Domains\Lien\Waivers\WaiverIntent;
use App\Domains\Lien\Waivers\WaiverStateRegistry;
use App\Support\Analytics\Gtag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Where every "create a waiver" button on the marketing pages lands. The
 * visitor's choices (state, send vs collect, waiver type) are stored as a
 * WaiverIntent, then the visitor is sent down the shortest path to the
 * wizard: register for guests, business setup when there is no business
 * yet, otherwise the wizard itself. The intent survives registration so the
 * wizard opens already knowing what they came for.
 */
class WaiverStartController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'state' => ['required', 'string', 'size:2', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! WaiverStateRegistry::isSupported((string) $value)) {
                    $fail('Pick the state the project is in.');
                }
            }],
            'direction' => ['nullable', Rule::enum(WaiverDirection::class)],
            'kind' => ['nullable', Rule::enum(WaiverKind::class)],
            // The marketing page the form sat on: an internal path only.
            'from' => ['nullable', 'string', 'max:255', 'regex:#^/[A-Za-z0-9\-_/]*$#'],
            // Set on links in our own emails: the visitor already has an account.
            'returning' => ['nullable', 'boolean'],
        ], [
            'state.required' => 'Pick the state the project is in.',
            'state.size' => 'Pick the state the project is in.',
        ]);

        $from = $validated['from'] ?? null;

        $intent = WaiverIntent::store([
            'state' => $validated['state'],
            'direction' => $validated['direction'] ?? null,
            'kind' => $validated['kind'] ?? null,
            'source' => $from,
        ]);

        Gtag::queue('waiver_starter_submit', array_filter([
            'state' => $intent['state'],
            'direction' => $intent['direction'],
            'waiver_kind' => $intent['kind'],
            'source' => $intent['source'],
        ]));

        $user = $request->user();

        if ($user === null) {
            // A "finish your waiver" email link: log in, then come back through
            // here so the choices above carry into the wizard.
            if ($request->boolean('returning')) {
                return redirect()->guest(route('login'));
            }

            // Attribution normally comes from the /register referer. The
            // starter names its page explicitly so the signup is credited to
            // the waiver pages even when the referer is stripped.
            if ($from !== null) {
                session()->put('signup_landing_path', $from);
                session()->put('signup_landing_url', $request->getSchemeAndHttpHost().$from);
            }

            return redirect()->route('register');
        }

        $business = $user->currentBusiness();

        if ($business === null) {
            return redirect()->route('portal.select-business');
        }

        if (! $business->isOnboardingComplete()) {
            return redirect()->route('portal.onboarding');
        }

        return redirect()->route('lien.waivers.create');
    }
}
