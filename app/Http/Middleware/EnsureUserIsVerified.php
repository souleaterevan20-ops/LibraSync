<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If the user is logged in but NOT verified yet...
        if (auth()->check() && !auth()->user()->is_verified) {
            // Send them to a "waiting room" page
            return redirect()->route('pending.approval');
        }

        // If the Super Admin has disabled this account, kick them out immediately.
        if (auth()->check() && !auth()->user()->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('status', 'Your account has been disabled. Please contact the library administrator.');
        }

        return $next($request);
    }
}
