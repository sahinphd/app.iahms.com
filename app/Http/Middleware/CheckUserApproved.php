<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (!$user->is_approved || $user->is_suspended) {
                Auth::logout();

                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $message = $user->is_suspended
                    ? 'Your account is suspended.'
                    : 'Your account is pending administrator approval.';

                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 403);
                }

                return redirect()->route('login')->withErrors([
                    'email' => $message,
                ]);
            }
        }

        return $next($request);
    }
}
