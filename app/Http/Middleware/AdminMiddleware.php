<?php

namespace App\Http\Middleware;

use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::guard('admin')->check() && auth('admin')->user()->status == 1) {
            return $next($request);
        }
        auth()->guard('admin')->logout();
//        Toastr::error(\App\CentralLogics\translate('you_have_been_blocked!'));
        return redirect()->route('admin.auth.login');
    }
}
