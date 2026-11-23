<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

class AccountPasswordController extends Controller
{
    /**
     * Single POST /change-password endpoint for both attendee and staff forms.
     *
     * The two forms use different field names, so the submitted field names
     * select the right account domain. The authenticated guard is used as the
     * fallback. This removes the old duplicate-route collision without
     * changing either form's action or field names.
     */
    public function change(Request $request)
    {
        if ($request->exists('current_password') || $request->exists('confirm_password')) {
            if (!Auth::check()) {
                return redirect('/login');
            }

            return app(UserController::class)->changePassword($request);
        }

        if ($request->exists('old_password') || $request->exists('password_confirmation')) {
            if (!Auth::guard('appuser')->check()) {
                return redirect('/user/login');
            }

            return app(FrontendController::class)->changeUserPassword($request);
        }

        if (Auth::check()) {
            return app(UserController::class)->changePassword($request);
        }

        if (Auth::guard('appuser')->check()) {
            return app(FrontendController::class)->changeUserPassword($request);
        }

        return redirect('/user/login');
    }
}
