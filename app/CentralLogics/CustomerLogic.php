<?php

namespace App\CentralLogics;

use App\Model\BusinessSetting;
use App\Model\PointTransitions;
use App\Model\WalletBonus;
use App\User;
use App\Model\WalletTransaction;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerLogic{

    public static function create_wallet_transaction($user_id, float $amount, $transaction_type, $referance)
    {

        if(BusinessSetting::where('key','wallet_status')->first()->value != 1) return false;

        $user = User::find($user_id);
        $current_balance = $user->wallet_balance;

        $wallet_transaction = new WalletTransaction();
        $wallet_transaction->user_id = $user->id;
        $wallet_transaction->transaction_id = Str::random('30');
        $wallet_transaction->reference = $referance;
        $wallet_transaction->transaction_type = $transaction_type;

        $debit = 0.0;
        $credit = 0.0;

        if(in_array($transaction_type, ['add_fund_by_admin','add_fund','loyalty_point', 'referrer','add_fund_bonus']))
        {
            $credit = $amount;

            if($transaction_type == 'loyalty_point')
            {
                $credit = (int)($amount / BusinessSetting::where('key','loyalty_point_exchange_rate')->first()->value);
            }
        }
        else if($transaction_type == 'order_place')
        {
            $debit = $amount;
        }

        $wallet_transaction->credit = $credit;
        $wallet_transaction->debit = $debit;
        $wallet_transaction->balance = $current_balance + $credit - $debit;
        $wallet_transaction->created_at = now();
        $wallet_transaction->updated_at = now();
        $user->wallet_balance = $current_balance + $credit - $debit;

        try{
            DB::beginTransaction();
            $user->save();
            $wallet_transaction->save();
            DB::commit();
            if(in_array($transaction_type, ['loyalty_point','order_place','add_fund_by_admin', 'referrer', 'add_fund', 'add_fund_bonus'])) return $wallet_transaction;
            return true;
        }catch(\Exception $ex)
        {
            info($ex);
            DB::rollback();

            return false;
        }
        return false;
    }

    public static function create_loyalty_point_transaction($user_id, $referance, $amount, $transaction_type)
    {
        $settings = array_column(BusinessSetting::whereIn('key',['loyalty_point_status','loyalty_point_exchange_rate','loyalty_point_item_purchase_point'])->get()->toArray(), 'value','key');
        if($settings['loyalty_point_status'] != 1)
        {
            return true;
        }

        $credit = 0;
        $debit = 0;
        $user = User::find($user_id);

        if (!isset($user)){
            return false;
        }

        $loyalty_point_transaction = new PointTransitions();
        $loyalty_point_transaction->user_id = $user->id;
        $loyalty_point_transaction->transaction_id = Str::random('30');
        $loyalty_point_transaction->reference = $referance;
        $loyalty_point_transaction->type = $transaction_type;

        if($transaction_type=='order_place')
        {
            $credit = (int)($amount * $settings['loyalty_point_item_purchase_point']/100);
        }
        else if($transaction_type=='point_to_wallet')
        {
            $debit = $amount;
        }

        $current_balance = $user->point + $credit - $debit;
        $loyalty_point_transaction->amount = $current_balance;
        $loyalty_point_transaction->credit = $credit;
        $loyalty_point_transaction->debit = $debit;
        $loyalty_point_transaction->created_at = now();
        $loyalty_point_transaction->updated_at = now();
        $user->point = $current_balance;

        try{
            DB::beginTransaction();
            $user->save();
            $loyalty_point_transaction->save();
            DB::commit();
            return true;
        }catch(\Exception $ex)
        {
            info($ex);
            DB::rollback();

            return false;
        }
        return false;
    }


    public static function referral_earning_wallet_transaction($user_id, $transaction_type, $referance)
    {
        $user = User::find($referance);
        $current_balance = $user->wallet_balance;

        $debit = 0.0;
        $credit = 0.0;
        $amount = BusinessSetting::where('key','ref_earning_exchange_rate')->first()->value?? 0;
        $credit = $amount;

        $wallet_transaction = new WalletTransaction();
        $wallet_transaction->user_id = $user->id;
        $wallet_transaction->transaction_id = Str::random('30');
        $wallet_transaction->reference = $user_id;
        $wallet_transaction->transaction_type = $transaction_type;
        $wallet_transaction->credit = $credit;
        $wallet_transaction->debit = $debit;
        $wallet_transaction->balance = $current_balance + $credit;
        $wallet_transaction->created_at = now();
        $wallet_transaction->updated_at = now();
        $user->wallet_balance = $current_balance + $credit;

        try{
            DB::beginTransaction();
            $user->save();
            $wallet_transaction->save();
            DB::commit();
        }catch(\Exception $ex)
        {
            info($ex);
            DB::rollback();

            return false;
        }
    }

    public static function loyalty_point_wallet_transfer_transaction($user_id, $point, $amount) {

        DB::transaction(function () use ($user_id, $point, $amount) {

            //Customer (loyalty_point update)
            $user = User::find($user_id);
            $current_wallet_balance = $user->wallet_balance;
            $current_point = $user->point;
            //dd($current_wallet_balance);

            $user->point -= $point;
            $user->wallet_balance += $amount;
            $user->save();

            WalletTransaction::create([
                'user_id' => $user_id,
                'transaction_id' => Str::random('30'),
                'reference' => null,
                'transaction_type' => 'loyalty_point_to_wallet',
                'debit' => 0,
                'credit' => $amount,
                'balance' => $current_wallet_balance + $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            PointTransitions::create([
                'user_id' => $user_id,
                'transaction_id' => Str::random('30'),
                'reference' => null,
                'type' => 'loyalty_point_to_wallet',
                'debit' => $point,
                'credit' => 0,
                'amount' => $current_point - $point,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });
    }

    public static function add_to_wallet($customer_id, float $amount)
    {
        $customer = User::find($customer_id);
        $fcm_token = $customer ? $customer->cm_firebase_token : '';
        $bonus_amount = self::add_to_wallet_bonus($customer_id, $amount);
        $reference = 'add-fund';

        $wallet_transaction = self::create_wallet_transaction($customer_id, $amount, 'add_fund', $reference);

        if ($wallet_transaction) {
            $local = $customer ? $customer->language_code : 'en';
            $restaurant_name = Helpers::get_business_settings('restaurant_name');
            $bonus_value = '';

            if ($bonus_amount > 0){
                $bonus_transaction = self::create_wallet_transaction($customer_id, $bonus_amount, 'add_fund_bonus', 'add-fund-bonus');

                if ($bonus_transaction){
                    $bonus_message = Helpers::order_status_update_message(ADD_WALLET_BONUS_MESSAGE);

                    if ($local != 'en'){
                        $translated_message = BusinessSetting::with('translations')->where(['key' => ADD_WALLET_BONUS_MESSAGE])->first();
                        if (isset($translated_message->translations)){
                            foreach ($translated_message->translations as $translation){
                                if ($local == $translation->locale){
                                    $bonus_message = $translation->value;
                                }
                            }
                        }
                    }
                    $bonus_value = Helpers::text_variable_data_format(value:$bonus_message, user_name: $customer->f_name. ' '. $customer->l_name, restaurant_name: $restaurant_name);
                }
            }

            $message = Helpers::order_status_update_message(ADD_WALLET_MESSAGE);

            if ($local != 'en'){
                $translated_message = BusinessSetting::with('translations')->where(['key' => ADD_WALLET_MESSAGE])->first();
                if (isset($translated_message->translations)){
                    foreach ($translated_message->translations as $translation){
                        if ($local == $translation->locale){
                            $message = $translation->value;
                        }
                    }
                }
            }
            $value = Helpers::text_variable_data_format(value:$message, user_name: $customer->f_name. ' '. $customer->l_name, restaurant_name: $restaurant_name);

            try {
                if ($value) {
                    $data = [
                        'title' => translate('wallet'),
                        'description' => $bonus_amount > 0 ? Helpers::set_symbol($amount) . ' ' . $value. ', '. Helpers::set_symbol($bonus_amount). ' '. $bonus_value : Helpers::set_symbol($amount) . ' ' . $value,
                        'order_id' => '',
                        'image' => '',
                        'type' => 'order_status',
                    ];
                    if (isset($fcm_token)) {
                        Helpers::send_push_notif_to_device($fcm_token, $data);
                    }
                }
                return true;
            } catch (\Exception $e) {
                Toastr::warning(translate('Push notification send failed for Customer!'));
            }
        }

        return false;

    }

    public static function add_to_wallet_bonus($customer_id, float $amount)
    {
        $bonuses = WalletBonus::active()
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->where('minimum_add_amount', '<=', $amount)
            ->get();

        $bonuses = $bonuses->where('minimum_add_amount', $bonuses->max('minimum_add_amount'));

        foreach ($bonuses as $key=>$item) {
            $item->applied_bonus_amount = $item->bonus_type == 'percentage' ? ($amount*$item->bonus_amount)/100 : $item->bonus_amount;

            //max bonus check
            if($item->bonus_type == 'percentage' && $item->applied_bonus_amount > $item->maximum_bonus_amount) {
                $item->applied_bonus_amount = $item->maximum_bonus_amount;
            }
        }

        return $bonuses->max('applied_bonus_amount') ?? 0;
    }

}
