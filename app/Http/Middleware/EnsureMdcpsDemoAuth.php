<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gates the isolated MDCPS demo CMS using a simple session flag.
 *
 * There is no database or real auth system; a successful login sets
 * `mdcps_demo_authed` on the session and this middleware enforces it for
 * the /mdcps-demo/admin pages.
 */
class EnsureMdcpsDemoAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('mdcps_demo_authed')) {
            return redirect()->route('mdcps-demo.admin.login');
        }

        return $next($request);
    }
}
