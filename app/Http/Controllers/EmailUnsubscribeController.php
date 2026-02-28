<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailUnsubscribeController extends Controller
{
    public function preferences(Request $request, User $user): View
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired link.');
        }

        return view('mail.email-preferences', ['user' => $user]);
    }
}
