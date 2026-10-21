<?php

namespace App\Http\Controllers;

use App\Domains\Lien\Support\LienProductIntent;
use App\Domains\Lien\Support\LienProducts;
use App\Support\SignupIntent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Where every "Start" button in the lien product chooser lands (EREG-143).
 * The product and state are stored as a LienProductIntent, then the visitor
 * takes the shortest path into the existing project and deadline flow:
 * sign-up for guests (with the sign-up intent set to liens, so business
 * setup sends them to lien onboarding), business setup when it is not done,
 * otherwise a new project when they have none, their only project, or the
 * project list to pick one. The project page then offers the product first.
 */
class LienStartController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $product = (string) $request->query('product', '');

        abort_unless(LienProducts::isOrderable($product), 404);

        $intent = LienProductIntent::store([
            'product' => $product,
            'state' => $request->query('state'),
        ]);

        $user = $request->user();

        if ($user === null) {
            SignupIntent::store([
                'product' => 'liens',
                'intent' => LienProducts::intentSlug($product),
                'state' => $intent['state'],
            ]);

            return redirect()->route('register');
        }

        $business = $user->currentBusiness();

        if ($business === null) {
            return redirect()->route('portal.select-business');
        }

        if (! $business->isOnboardingComplete()) {
            return redirect()->route('portal.onboarding');
        }

        $projects = $business->lienProjects()->latest('id')->limit(2)->get();

        return match ($projects->count()) {
            0 => redirect()->route('lien.projects.create'),
            1 => redirect()->route('lien.projects.show', $projects->first()),
            default => redirect()->route('lien.projects.index'),
        };
    }
}
