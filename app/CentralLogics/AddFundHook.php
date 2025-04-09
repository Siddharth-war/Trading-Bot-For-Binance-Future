<?php

use App\CentralLogics\CustomerLogic;


if (!function_exists('add_fund_success')) {
    /**
     * @param $data
     * @return void
     */
    function add_fund_success($data): void
    {
        $customer_id = $data['payer_id'];
        $amount = $data['payment_amount'];
        CustomerLogic::add_to_wallet($customer_id, $amount);

    }
}

if (!function_exists('add_fund_fail')) {
    /**
     * @param $data
     * @return void
     */
    function add_fund_fail($data): void
    {
        //
    }
}

