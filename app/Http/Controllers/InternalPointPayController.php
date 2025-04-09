<?php

namespace App\Http\Controllers;

use App\Model\BusinessSetting;
use App\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InternalPointPayController extends Controller
{
    public function payment(Request $request): Redirector|RedirectResponse|Application
    {
        $callback = $request['callback'];

        $user = User::find($request['customer_id']);
        $value = BusinessSetting::where(['key' => 'point_per_currency'])->first()->value;
        $order_point = $request['order_amount'] * $value;
        $tr_ref = 'payment_' . Str::random('15');

        //token string generate
        $transaction_reference = $tr_ref;
        $token_string = 'payment_method=internal_point&&transaction_reference=' . $transaction_reference;

        if ($request['order_amount'] <= $order_point) {
            User::where(['id' => $user['id']])->decrement('point', $order_point);
            DB::table('point_transitions')->insert([
                'user_id' => $user['id'],
                'description' => 'paid for transaction ID : ' . $tr_ref . '.',
                'type' => 'point_out',
                'amount' => $order_point,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            //success
            if ($callback != null) {
                return redirect($callback . '/success' . '?token=' . base64_encode($token_string));
            } else {
                return \redirect()->route('payment-success', ['token' => base64_encode($token_string)]);
            }

        } else {
            //fail
            if ($callback != null) {
                return redirect($callback . '/fail' . '?token=' . base64_encode($token_string));
            } else {
                return \redirect()->route('payment-fail', ['token' => base64_encode($token_string)]);
            }
        }

    }
}
