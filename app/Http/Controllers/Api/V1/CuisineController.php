<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cuisine;
use Illuminate\Http\JsonResponse;


class CuisineController extends Controller
{
    public function __construct(
        private Cuisine $cuisine
    )
    {}

    /**
     * @return JsonResponse
     */
    public function getCuisines(): JsonResponse
    {
        $cuisines = $this->cuisine::active()->orderBy('priority', 'ASC')->get();
        return response()->json($cuisines, 200);
    }
}
