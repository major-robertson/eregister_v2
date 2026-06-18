<?php

namespace App\Http\Controllers\Demo\Mdcps;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Isolated front-end-only demo for the Miami-Dade school website proposal.
 *
 * The public homepage and the login screen are open; the CMS pages are
 * gated by a simple session flag (see EnsureMdcpsDemoAuth). All content
 * state is persisted client-side in localStorage; there is intentionally
 * no database, model, or business logic here.
 */
class MdcpsDemoController extends Controller
{
    public function home(): View
    {
        return view('demo.mdcps.home');
    }

    public function publicCalendar(): View
    {
        return view('demo.mdcps.calendar');
    }

    public function showLogin(): View|RedirectResponse
    {
        if (request()->session()->get('mdcps_demo_authed')) {
            return redirect()->route('mdcps-demo.admin.dashboard');
        }

        return view('demo.mdcps.admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $usernameMatches = hash_equals(
            (string) config('mdcps_demo.username'),
            $credentials['username']
        );
        $passwordMatches = hash_equals(
            (string) config('mdcps_demo.password'),
            $credentials['password']
        );

        if (! $usernameMatches || ! $passwordMatches) {
            return back()
                ->withInput(['username' => $credentials['username']])
                ->withErrors(['username' => 'Those sandbox credentials were not recognized.']);
        }

        $request->session()->regenerate();
        $request->session()->put('mdcps_demo_authed', true);

        return redirect()->route('mdcps-demo.admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('mdcps_demo_authed');

        return redirect()->route('mdcps-demo.admin.login');
    }

    public function admin(): View
    {
        return view('demo.mdcps.admin.dashboard');
    }

    public function calendar(): View
    {
        return view('demo.mdcps.admin.calendar');
    }

    public function alert(): View
    {
        return view('demo.mdcps.admin.alert');
    }

    public function media(): View
    {
        return view('demo.mdcps.admin.media');
    }
}
