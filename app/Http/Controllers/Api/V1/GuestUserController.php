<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\GuestUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GuestUserController extends Controller
{
    public function __construct(
        private GuestUser $guestUser,
    ){}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function guestStore(Request $request): JsonResponse
    {
        $guest = $this->guestUser;
        $guest->ip_address = $request->ip();
        $guest->fcm_token = $request->fcm_token;
        $guest->save();

        return response()->json(['guest' => $guest], 200);
    }
}
