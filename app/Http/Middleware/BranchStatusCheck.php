<?php

namespace App\Http\Middleware;

use Closure;
use Brian2694\Toastr\Facades\Toastr;

class BranchStatusCheck
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
        if (auth('branch')->user()->status == 1) {
            return $next($request);
        }
        auth()->guard('branch')->logout();
        Toastr::warning(\App\CentralLogics\translate('account_is_disabled!'));
        return redirect()->route('branch.auth.login');

    }
}
