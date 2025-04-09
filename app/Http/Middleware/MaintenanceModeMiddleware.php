<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MaintenanceModeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $maintenance = Cache::get('maintenance');
        if ($maintenance) {
            $maintenanceStatus = $maintenance['status'];

            if ($maintenanceStatus) {
                if ($maintenance['branch_panel']) {
                    if (isset($maintenance['maintenance_duration']) && $maintenance['maintenance_duration'] == 'until_change') {
                        abort(503, '', [
                        'maintenanceMessage' => $maintenance['maintenance_messages']['maintenance_message'] ?? 'Maintenance mode is active.',
                        'messageBody' => $maintenance['maintenance_messages']['message_body'] ?? '',
                        'businessNumber' => $maintenance['maintenance_messages']['business_number'] ?? '',
                        'businessEmail' => $maintenance['maintenance_messages']['business_email'] ?? ''
                    ]);
                    } else {
                        if (isset($maintenance['start_date']) && isset($maintenance['end_date'])) {
                            $start = Carbon::parse($maintenance['start_date']);
                            $end = Carbon::parse($maintenance['end_date']);
                            $today = Carbon::now();
                            if ($today->between($start, $end)) {
                                abort(503, '', [
                                    'maintenanceMessage' => $maintenance['maintenance_messages']['maintenance_message'] ?? 'Maintenance mode is active.',
                                    'messageBody' => $maintenance['maintenance_messages']['message_body'] ?? '',
                                    'businessNumber' => $maintenance['maintenance_messages']['business_number'] ?? '',
                                    'businessEmail' => $maintenance['maintenance_messages']['business_email'] ?? ''
                                ]);
                            }
                        }
                    }
                }
            }
        }
        return $next($request);
    }
}
