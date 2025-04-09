<?php

namespace App\Http\Middleware;

use App\Model\BusinessSetting;
use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppActivation
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param $app_id
     * @return RedirectResponse|mixed
     */
    public function handle(Request $request, Closure $next, $app_id)
    {
        if (env('APP_MODE') != 'live') {
            return $next($request);
        }

        if ($app_id == 'get_from_route') {
            $software_id = $request->route('app_id');
        } else {
            $software_id = $app_id;
        }

        $activated_app = json_decode(BusinessSetting::where('key', 'app_activation')->first()->value ?? '[]');
        $found = 0;
        foreach ($activated_app as $key => $item) {
            if ($item->software_id == $software_id) {
                $found = 1;
            }
        }

        if ($found) {
            if (request()->is('admin/app-activate/*')) {
                Toastr::success('App activated successfully');
                return redirect(session('stored_url'));
            }
            return $next($request);
        } else {
            if (request()->is('admin/app-activate/*')) {
                return $next($request);
            } elseif (request()->is('api/*')) {
                return response()->json('please activate your app first');
            } elseif (request()->is('branch/*')) {
                Toastr::info('Please talk to the administrator');
                return back();
            }
        }

        session()->put('stored_url', url()->current());
        return redirect()->route('admin.app-activate', [$software_id]);
    }
}
