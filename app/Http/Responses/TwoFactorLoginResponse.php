<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): Response
    {
        $user = $request->user();

        // Get all admin-level role names
        $adminRoles = Role::pluck('name')->toArray();

        // If user has any admin role, redirect to admin dashboard
        if ($user && $user->hasAnyRole($adminRoles)) {
            return $this->redirectTo($request, route('admin.home'));
        }

        // Default redirect for regular users
        return $this->redirectTo($request, config('fortify.home'));
    }

    /**
     * Create a redirect response.
     */
    protected function redirectTo(Request $request, string $path): Response
    {
        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended($path);
    }
}
