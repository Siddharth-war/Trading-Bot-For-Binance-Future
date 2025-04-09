<?php

namespace App\Traits;

use App\CentralLogics\Helpers;
use App\Models\Setting;
use Illuminate\Support\Carbon;

trait HelperTrait
{
    public function checkMaintenanceMode(): array
    {
        $maintenanceSystemArray = ['branch_panel', 'customer_app', 'web_app', 'deliveryman_app'];
        $selectedMaintenanceSystem = Helpers::get_business_settings('maintenance_system_setup')?? [];

        $maintenanceSystem = [];
        foreach ($maintenanceSystemArray as $system) {
            $maintenanceSystem[$system] = in_array($system, $selectedMaintenanceSystem) ? 1 : 0;
        }

        $selectedMaintenanceDuration = Helpers::get_business_settings('maintenance_duration_setup') ?? [];
        $maintenanceStatus = (integer)(Helpers::get_business_settings('maintenance_mode') ?? 0);

        $status = 0;
        if ($maintenanceStatus == 1) {
            if (isset($selectedMaintenanceDuration['maintenance_duration']) && $selectedMaintenanceDuration['maintenance_duration'] == 'until_change') {
                $status = $maintenanceStatus;
            } else {
                if (isset($selectedMaintenanceDuration['start_date']) && isset($selectedMaintenanceDuration['end_date'])) {
                    $start = \Carbon\Carbon::parse($selectedMaintenanceDuration['start_date']);
                    $end = Carbon::parse($selectedMaintenanceDuration['end_date']);
                    $today = Carbon::now();
                    if ($today->between($start, $end)) {
                        $status = 1;
                    }
                }
            }
        }

        return [
            'maintenance_status' => $status,
            'selected_maintenance_system' => $maintenanceSystem,
            'maintenance_messages' => Helpers::get_business_settings('maintenance_message_setup') ?? [],
            'maintenance_type_and_duration' => $selectedMaintenanceDuration,
        ];
    }

    public function getActiveSMSGatewayCount()
    {
        $dataValues = Setting::where('settings_type', 'sms_config')->get();
        $count = 0;
        foreach ($dataValues as $gateway) {
            $status = isset($gateway->live_values['status']) ? (int)$gateway->live_values['status'] : 0;
            if ($status == 1) {
                $count++;
            }
        }

        $firebaseOTPVerification = Helpers::get_business_settings('firebase_otp_verification');
        $firebaseOTPVerificationStatus = (integer)($firebaseOTPVerification ? $firebaseOTPVerification['status'] : 0);
        if ($firebaseOTPVerificationStatus == 1){
            $count++;
        }

        return $count;
    }
}
