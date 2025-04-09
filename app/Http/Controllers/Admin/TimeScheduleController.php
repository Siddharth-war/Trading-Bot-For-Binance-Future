<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Model\TimeSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Support\Renderable;

class TimeScheduleController extends Controller
{
    public function __construct(
        private TimeSchedule $timeSchedule,
    )
    {
    }

    /**
     * @return Renderable
     */
    public function timeScheduleIndex(): Renderable
    {
        $schedules = $this->timeSchedule->get();
        return view('admin-views.business-settings.time-schedule-index', compact('schedules'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addSchedule(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ], [
            'end_time.after' => translate('End time must be after the start time')
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $temp = $this->timeSchedule->where('day', $request->day)
            ->where(function ($q) use ($request) {
                return $q->where(function ($query) use ($request) {
                    return $query->where('opening_time', '<=', $request->start_time)->where('closing_time', '>=', $request->start_time);
                })->orWhere(function ($query) use ($request) {
                    return $query->where('opening_time', '<=', $request->end_time)->where('closing_time', '>=', $request->end_time);
                });
            })
            ->first();

        if (isset($temp)) {
            return response()->json(['errors' => [
                ['code' => 'time', 'message' => translate('schedule_overlapping_warning')]
            ]]);
        }

        $timeSchedule = $this->timeSchedule->insert(['day' => $request->day, 'opening_time' => $request->start_time, 'closing_time' => $request->end_time]);

        $schedules = $this->timeSchedule->get();
        return response()->json(['view' => view('admin-views.business-settings.partials._schedule', compact('schedules'))->render()]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function removeSchedule(Request $request): JsonResponse
    {
        $schedule = $this->timeSchedule->find($request['schedule_id']);
        if (!$schedule) {
            return response()->json([], 404);
        }
        $restaurant = $schedule->restaurant;
        $schedule->delete();

        $schedules = $this->timeSchedule->get();
        return response()->json([
            'view' => view('admin-views.business-settings.partials._schedule', compact('schedules'))->render(),
        ]);
    }
}
