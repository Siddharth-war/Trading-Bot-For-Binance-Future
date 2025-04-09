<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\CustomerLogic;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\BusinessSetting;
use App\Model\WalletBonus;
use App\Model\WalletTransaction;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerWalletController extends Controller
{
    public function __construct(
        private User              $user,
        private BusinessSetting   $businessSetting,
        private WalletTransaction $walletTransaction,
        private WalletBonus       $walletBonus
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function transferLoyaltyPointToWallet(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'point' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $user = $this->user->find($request->user()->id);
        if ($request['point'] > $user->point) {
            return response()->json(['errors' => [['code' => 'wallet', 'message' => translate('Your point in not sufficient!')]]], 401);
        }

        $minimumPoint = $this->businessSetting->where(['key' => 'loyalty_point_minimum_point'])->first()->value;
        if ($request['point'] < $minimumPoint) {
            return response()->json(['errors' => [['code' => 'wallet', 'message' => translate('Your point in not sufficient!')]]], 401);
        }

        $loyaltyPointExchangeRate = $this->businessSetting->where(['key' => 'loyalty_point_exchange_rate'])->first()->value;
        $loyaltyAmount = $request['point'] / $loyaltyPointExchangeRate;

        CustomerLogic::loyalty_point_wallet_transfer_transaction($user->id, $request['point'], $loyaltyAmount);

        return response()->json(['message' => translate('transfer success')], 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function walletTransactions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'limit' => 'required',
            'offset' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $transactionType = $request['transaction_type'];

        $paginator = $this->walletTransaction
            ->when(isset($transactionType) && ($transactionType == 'add_fund_by_admin'), function ($query) {
                return $query->where('transaction_type', 'add_fund_by_admin');
            })
            ->when(isset($transactionType) && ($transactionType == 'add_fund'), function ($query) {
                return $query->where('transaction_type', 'add_fund');
            })
            ->when(isset($transactionType) && ($transactionType == 'loyalty_point_to_wallet'), function ($query) {
                return $query->where('transaction_type', 'loyalty_point_to_wallet');
            })
            ->when(isset($transactionType) && ($transactionType == 'referral_order_place'), function ($query) {
                return $query->where('transaction_type', 'referral_order_place');
            })
            ->when(isset($transactionType) && ($transactionType == 'add_fund_bonus'), function ($query) {
                return $query->where('transaction_type', 'add_fund_bonus');
            })
            ->when(isset($transactionType) && ($transactionType == 'order_place'), function ($query) {
                return $query->where('transaction_type', 'order_place');
            })
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->limit, ['*'], 'page', $request->offset);

        $data = [
            'total_size' => $paginator->total(),
            'limit' => $request->limit,
            'offset' => $request->offset,
            'data' => $paginator->items()
        ];

        return response()->json($data, 200);
    }


    /**
     * @return JsonResponse
     */
    public function walletBonusList(): JsonResponse
    {
        $bonuses = $this->walletBonus->active()
            ->where('start_date', '<=', now()->format('Y-m-d'))
            ->where('end_date', '>=', now()->format('Y-m-d'))
            ->latest()
            ->get();

        return response()->json($bonuses, 200);
    }
}
