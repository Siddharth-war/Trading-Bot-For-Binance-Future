<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        switch ($guard) {
            case 'admin':
                if (Auth::guard($guard)->check()) {
                    return redirect()->route('admin.dashboard');
                }
                break;
            case 'branch':
                if (Auth::guard($guard)->check()) {
                    return redirect()->route('branch.dashboard');
                }
                break;
            default:
                if (Auth::guard($guard)->check()) {
                    return response()->json([],404);
                }
                break;
        }

        return $next($request);
    }
}
