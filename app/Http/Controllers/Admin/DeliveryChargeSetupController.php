<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Model\Branch;
use App\CentralLogics\Helpers;
use App\Models\DeliveryChargeByArea;
use App\Models\DeliveryChargeSetup;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use OpenSpout\Common\Exception\InvalidArgumentException;
use OpenSpout\Common\Exception\IOException;
use OpenSpout\Common\Exception\UnsupportedTypeException;
use OpenSpout\Writer\Exception\WriterNotOpenedException;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;


class DeliveryChargeSetupController extends Controller
{
    public function __construct(
        private DeliveryChargeSetup $deliveryChargeSetup,
        private DeliveryChargeByArea $deliveryChargeByArea,
        private Branch $branch,
    )
    {}

    /**
     * @param Request $request
     * @return Application|Factory|View|\Illuminate\Foundation\Application
     */
    public function deliveryFeeSetup(Request $request): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $search = $request->input('search');

        $branches = $this->branch
            ->with(['delivery_charge_setup', 'delivery_charge_by_area' => function ($query) use ($search) {
            if ($search) {
                $query->where('area_name', 'LIKE', "%{$search}%");
            }
        }])->get(['id', 'name', 'status']);

        return view('admin-views.business-settings.restaurant.delivery-fee', compact('branches'));
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeKilometerWiseDeliveryCharge(Request $request): RedirectResponse
    {
        $request->validate([
            'branch_id' => 'required',
            'delivery_charge_per_kilometer' => 'required|numeric|min:0|max:99999999',
            'minimum_delivery_charge' => 'required|numeric|min:0|max:99999999',
            'minimum_distance_for_free_delivery' => 'required|numeric|min:0|max:99999999',
        ]);

        $this->deliveryChargeSetup->updateOrCreate([
            'branch_id' => (integer) $request['branch_id']
        ], [
                'branch_id' => (integer) $request['branch_id'],
                'delivery_charge_per_kilometer' => $request['delivery_charge_per_kilometer'],
                'minimum_delivery_charge' => $request['minimum_delivery_charge'],
                'minimum_distance_for_free_delivery' => $request['minimum_distance_for_free_delivery'],
            ]
        );

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function storeFixedDeliveryCharge(Request $request): RedirectResponse
    {
        $request->validate([
            'branch_id' => 'required',
            'fixed_delivery_charge' => 'required|numeric|min:0|max:99999999',
        ]);

        $this->deliveryChargeSetup->updateOrCreate([
            'branch_id' => (integer) $request['branch_id']
        ], [
                'branch_id' => (integer) $request['branch_id'],
                'fixed_delivery_charge' => $request['fixed_delivery_charge'],
            ]
        );

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changeDeliveryChargeType(Request $request): JsonResponse
    {
        $branchId = (integer) $request['branch_id'];
        $status = (boolean) $request['status'];

        if ($status) {
            $googleMapStatus = Helpers::get_business_settings('google_map_status');
            if ($request['delivery_charge_type'] == 'distance' && $googleMapStatus != 1){
                return response()->json(['status' => false, 'error' => translate('Can not change delivery charge to distance while Google Map status is off. Please turn on the Google Map status first.')], 200);
            }

            $data = $this->deliveryChargeSetup->updateOrCreate(
                ['branch_id' => $branchId],
                ['delivery_charge_type' => $request['delivery_charge_type']]
            );
        } else {
            $newDeliveryChargeType = $request['new_delivery_charge_type'];

            if (!$newDeliveryChargeType) {
                return response()->json(['status' => false, 'error' => translate('New delivery charge type must be selected when deactivating the current setup.')], 200);
            }

            if ($newDeliveryChargeType == 'area') {
                $areaCount = $this->deliveryChargeByArea->where(['branch_id' => $branchId])->count();
                if ($areaCount < 1){
                    return response()->json(['status' => false, 'error' => translate('You can not switch to Area/Zip code. At least one area must be added to switch to Area.')], 200);
                }
            }

            $googleMapStatus = Helpers::get_business_settings('google_map_status');
            if ($newDeliveryChargeType == 'distance' && $googleMapStatus != 1){
                return response()->json(['status' => false, 'error' => translate('Can not change delivery charge to distance while Google Map status is off. Please turn on the Google Map status first.')], 200);
            }

            $data = $this->deliveryChargeSetup->updateOrCreate(
                ['branch_id' => $branchId],
                ['delivery_charge_type' => $newDeliveryChargeType]
            );
        }

        return response()->json(['status' => true, 'message' => translate('Successfully updated'), 'data' => $data], 200);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function StoreAreaWiseDeliveryCharge(Request $request): RedirectResponse
    {
        $request->validate([
            'branch_id' => 'required',
            'area_name' => [
                'required',
                'max:255',
                Rule::unique('delivery_charge_by_areas')->where(function ($query) use ($request) {
                    return $query->where('branch_id', $request->branch_id);
                }),
            ],
            'delivery_charge' => 'required|numeric|min:0|max:99999999',
        ]);

        $deliveryChargeByArea = $this->deliveryChargeByArea;
        $deliveryChargeByArea->branch_id = (integer) $request['branch_id'];
        $deliveryChargeByArea->area_name = $request['area_name'];
        $deliveryChargeByArea->delivery_charge = $request['delivery_charge'];
        $deliveryChargeByArea->save();

        if ($request->has('change_status') && $request['change_status'] == 1){
            $this->deliveryChargeSetup->updateOrCreate(
                ['branch_id' => $request['branch_id']],
                ['delivery_charge_type' => 'area']
            );
        }

        Toastr::success(translate('Settings updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function deleteAreaDeliveryCharge(Request $request, $id): RedirectResponse
    {
        $areaCount = $this->deliveryChargeByArea->where(['branch_id' => $request['branch_id']])->count();
        if ($areaCount <= 1){
            Toastr::warning(translate('You cannot delete this area. At least one area must remain.'));
            return back();
        }

        $deliveryArea = $this->deliveryChargeByArea->find($id);
        $deliveryArea->delete();

        Toastr::success(translate('Area removed!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function editAreaDeliveryCharge(Request $request, $id): JsonResponse
    {
        $deliveryArea = $this->deliveryChargeByArea->find($id);
        return response()->json($deliveryArea);
    }

    /**
     * @param Request $request
     * @param $id
     * @return RedirectResponse
     */
    public function updateAreaDeliveryCharge(Request $request, $id): RedirectResponse
    {
        $deliveryArea = $this->deliveryChargeByArea->find($id);

        $request->validate([
            'area_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('delivery_charge_by_areas')->where(function ($query) use ($request, $deliveryArea) {
                    return $query->where('branch_id', $deliveryArea->branch_id)
                        ->where('id', '!=', $deliveryArea->id);
                }),
            ],
            'delivery_charge' => 'required|numeric|min:0|max:99999999',
        ]);

        $deliveryArea->area_name = $request['area_name'];
        $deliveryArea->delivery_charge = $request['delivery_charge'];
        $deliveryArea->save();

        Toastr::success(translate('Successfully updated!'));
        return back();
    }

    /**
     * @param Request $request
     * @param $branch_id
     * @return string|StreamedResponse
     * @throws IOException
     * @throws InvalidArgumentException
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     */
    public function exportAreaDeliveryCharge(Request $request, $branch_id): StreamedResponse|string
    {
        $search = $request->input('search');

        $deliveryAreas = $this->deliveryChargeByArea
            ->with('branch')
            ->where(['branch_id' => $branch_id])
            ->when($search, function ($query) use($search){
                $key = explode(' ', $search);
                foreach ($key as $value) {
                    $query->where('area_name', 'LIKE', "%{$value}%");
                }
            })
            ->latest()
            ->get();

        $data = [];

        foreach ($deliveryAreas as $area) {
            $data[] = [
                'Branch id' => $area->branch_id,
                'Branch Name' => $area->branch ? $area?->branch->name : '',
                'Area name/Zip Code' => $area->area_name,
                'Delivery Charge' => $area->delivery_charge,
            ];
        }
        return (new FastExcel($data))->download('delivery-areas.xlsx');
    }

    /**
     * @param Request $request
     * @param $branch_id
     * @return JsonResponse
     */
    public function importAreaDeliveryCharge(Request $request, $branch_id): JsonResponse
    {
        $request->validate([
            'area_file' => 'required|file|mimes:xlsx,xls|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $collections = (new FastExcel)->import($request->file('area_file'));

            $fieldArray = ['branch_id', 'area_name', 'delivery_charge'];
            if (count($collections) < 1) {
                return response()->json([
                    'status' => 'error',
                    'message' => translate('At least one area has to be imported.')
                ], 422);
            }

            foreach ($fieldArray as $field) {
                if (!array_key_exists($field, $collections->first())) {
                    return response()->json([
                        'status' => 'error',
                        'message' => translate($field) . translate(' must not be empty.')
                    ], 422);
                }
            }

            $branchIds = $collections->pluck('branch_id')->unique();
            $existingBranchIds = Branch::whereIn('id', $branchIds)->pluck('id')->toArray();

            foreach ($branchIds as $branchId) {
                if (!in_array($branchId, $existingBranchIds)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => translate('Branch ID ') . $branchId . translate(' does not exist. Please provide valid branch ID.')
                    ], 422);
                }
            }

            foreach ($collections as $key => $collection) {
                if (empty($collection['branch_id']) || empty($collection['area_name']) || empty($collection['delivery_charge'])) {
                    return response()->json([
                        'status' => 'error',
                        'message' => translate('Please fill all required fields in row') . ' ' . ($key + 2)
                    ], 422);
                }

                $existingArea = $this->deliveryChargeByArea
                    ->where('branch_id', $collection['branch_id'])
                    ->where('area_name', $collection['area_name'])
                    ->exists();

                if ($existingArea) {
                    return response()->json([
                        'status' => 'error',
                        'message' => translate('The area name ') . $collection['area_name'] . translate(' already exists for branch ID ') . $collection['branch_id'] . translate(' in row ') . ($key + 2)
                    ], 422);
                }

                $data[] = [
                    'branch_id' => $collection['branch_id'],
                    'area_name' => $collection['area_name'],
                    'delivery_charge' => $collection['delivery_charge'],
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            if ($request['type'] == 'replace') {
                foreach ($branchIds as $branchId) {
                    $this->deliveryChargeByArea->where(['branch_id' => $branchId])->delete();
                }
            }

            $this->deliveryChargeByArea->insert($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => translate('Area delivery charges imported successfully.')
            ], 200);
        } catch (\Exception $exception) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => translate('You have uploaded an incorrect format file, please upload the correct file.')
            ], 500);
        }
    }

    /**
     * @return JsonResponse
     */
    public function checkDistanceBasedDelivery(): JsonResponse
    {
        $hasDistanceBasedDelivery = $this->deliveryChargeSetup->where('delivery_charge_type', 'distance')->exists();
        return response()->json(['hasDistanceBasedDelivery' => $hasDistanceBasedDelivery]);
    }
}
