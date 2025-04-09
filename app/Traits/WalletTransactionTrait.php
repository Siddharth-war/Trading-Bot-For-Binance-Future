<?php
namespace App\Traits;

use App\CentralLogics\CustomerLogic;
use App\CentralLogics\Helpers;
use App\Model\WalletBonus;
use App\User;
use Brian2694\Toastr\Facades\Toastr;

trait WalletTransactionTrait
{
  /*  public function __construct(
        private User $user,
        private WalletBonus $wallet_bonus
    ){}

    public function add_to_wallet($customer_id, float $amount, $reference)
    {
        $customer = $this->user->find($customer_id);
        $bonus = $this->add_to_wallet_bonus($customer_id, $amount);
        $total_amount = $amount + $bonus;

        $wallet_transaction = CustomerLogic::create_wallet_transaction($customer_id, $total_amount, 'add_fund', $reference);

        if ($wallet_transaction) {
            $value = $bonus > 0 ? Helpers::order_status_update_message(ADD_WALLET_BONUS_MESSAGE) : Helpers::order_status_update_message(ADD_WALLET_MESSAGE);
            try {
                if ($value) {
                    $data = [
                        'title' => translate('Order'),
                        'description' => Helpers::set_symbol($total_amount) . ' ' . $value,
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

    public function add_to_wallet_bonus($customer_id, float $amount)
    {
        $bonuses = $this->wallet_bonus->active()
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
    }*/
}
