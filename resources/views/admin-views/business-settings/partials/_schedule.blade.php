<div class="card-body" id="schedule">
    @php($data=[])
    <?php
    foreach ($schedules as $schedule)
    {
        $data[$schedule->day][]=['id'=>$schedule->id,'start_time'=>$schedule->opening_time, 'end_time'=>$schedule->closing_time];
    }
    ?>
    <div class="time-schedule-row">
        <span class="time-schedule-date">{{translate('monday')}}</span>
        @if(isset($data['1']) && count($data['1']))
        <div class="d-flex flex-wrap align-items-center gap-4 gap-sm-2 gapx-30">
            @foreach ($data['1'] as $day)
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Opening_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['start_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Closing_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['end_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="badge badge-danger rounded-circle cursor-pointer" onclick="delete_schedule('{{route('admin.business-settings.restaurant.time_schedule_remove',['schedule_id'=>$day['id']])}}')">X</div>
                </div>
            @endforeach
        </div>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{translate('Offday')}}</span>
        @endif
        <span class="add-schedule-btn ml-3" data-toggle="modal" data-target="#exampleModal" data-dayid="1" data-day="{{translate('monday')}}"><i class="tio-add"></i></span>
    </div>

    <div class="time-schedule-row">
        <span class="time-schedule-date">{{translate('tuesday')}}</span>
        @if(isset($data['2']) && count($data['2']))
        <div class="d-flex flex-wrap align-items-center gap-4 gap-sm-2 gapx-30">
            @foreach ($data['2'] as $day)
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Opening_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['start_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Closing_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['end_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="badge badge-danger rounded-circle cursor-pointer" onclick="delete_schedule('{{route('admin.business-settings.restaurant.time_schedule_remove',['schedule_id'=>$day['id']])}}')">X</div>
                </div>
            @endforeach
        </div>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{translate('Offday')}}</span>
        @endif
        <span class="add-schedule-btn ml-3" data-toggle="modal" data-target="#exampleModal" data-dayid="2" data-day="{{translate('tuesday')}}"><i class="tio-add"></i></span>
    </div>

    <div class="time-schedule-row">
        <span class="time-schedule-date">{{translate('wednesday')}}</span>
        @if(isset($data['3']) && count($data['3']))
        <div class="d-flex flex-wrap align-items-center gap-4 gap-sm-2 gapx-30">
            @foreach ($data['3'] as $day)
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Opening_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['start_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Closing_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['end_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="badge badge-danger rounded-circle cursor-pointer" onclick="delete_schedule('{{route('admin.business-settings.restaurant.time_schedule_remove',['schedule_id'=>$day['id']])}}')">X</div>
                </div>
            @endforeach
        </div>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{translate('Offday')}}</span>
        @endif
        <span class="add-schedule-btn ml-3" data-toggle="modal" data-target="#exampleModal" data-dayid="3" data-day="{{translate('wednesday')}}"><i class="tio-add"></i></span>
    </div>

    <div class="time-schedule-row">
        <span class="time-schedule-date">{{translate('thursday')}}</span>
        @if(isset($data['4']) && count($data['4']))
        <div class="d-flex flex-wrap align-items-center gap-4 gap-sm-2 gapx-30">
            @foreach ($data['4'] as $day)
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Opening_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['start_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Closing_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['end_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="badge badge-danger rounded-circle cursor-pointer" onclick="delete_schedule('{{route('admin.business-settings.restaurant.time_schedule_remove',['schedule_id'=>$day['id']])}}')">X</div>
                </div>
            @endforeach
        </div>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{translate('Offday')}}</span>
        @endif
        <span class="add-schedule-btn ml-3" data-toggle="modal" data-target="#exampleModal" data-dayid="4" data-day="{{translate('thirsday')}}"><i class="tio-add"></i></span>
    </div>

    <div class="time-schedule-row">
        <span class="time-schedule-date">{{translate('friday')}}</span>
        @if(isset($data['5']) && count($data['5']))
        <div class="d-flex flex-wrap align-items-center gap-4 gap-sm-2 gapx-30">
            @foreach ($data['5'] as $day)
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Opening_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['start_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Closing_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['end_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="badge badge-danger rounded-circle cursor-pointer" onclick="delete_schedule('{{route('admin.business-settings.restaurant.time_schedule_remove',['schedule_id'=>$day['id']])}}')">X</div>
                </div>
            @endforeach
        </div>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{translate('Offday')}}</span>
        @endif
        <span class="add-schedule-btn ml-3" data-toggle="modal" data-target="#exampleModal" data-dayid="5" data-day="{{translate('friday')}}"><i class="tio-add"></i></span>
    </div>

    <div class="time-schedule-row">
        <span class="time-schedule-date">{{translate('saturday')}}</span>
        @if(isset($data['6']) && count($data['6']))
        <div class="d-flex flex-wrap align-items-center gap-4 gap-sm-2 gapx-30">
            @foreach ($data['6'] as $day)
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Opening_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['start_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Closing_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['end_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="badge badge-danger rounded-circle cursor-pointer" onclick="delete_schedule('{{route('admin.business-settings.restaurant.time_schedule_remove',['schedule_id'=>$day['id']])}}')">X</div>
                </div>
            @endforeach
        </div>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{translate('Offday')}}</span>
        @endif
        <span class="add-schedule-btn ml-3" data-toggle="modal" data-target="#exampleModal" data-dayid="6" data-day="{{translate('saturday')}}"><i class="tio-add"></i></span>
    </div>

    <div class="time-schedule-row">
        <span class="time-schedule-date">{{translate('sunday')}}</span>
        @if(isset($data['0']) && count($data['0']))
        <div class="d-flex flex-wrap align-items-center gap-4 gap-sm-2 gapx-30">
            @foreach ($data['0'] as $day)
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Opening_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['start_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="border rounded py-2 px-3">
                        <div class="d-flex gap-2">
                            <i class="tio-time mt-1"></i>
                            <div>
                                <div>{{translate('Closing_Time')}}</div>
                                <div>{{date(config('time_format'), strtotime($day['end_time']))}}</div>
                            </div>
                        </div>
                    </div>
                    <div class="badge badge-danger rounded-circle cursor-pointer" onclick="delete_schedule('{{route('admin.business-settings.restaurant.time_schedule_remove',['schedule_id'=>$day['id']])}}')">X</div>
                </div>
            @endforeach
        </div>
        @else
            <span class="btn btn-sm btn-outline-danger m-1 disabled">{{translate('Offday')}}</span>
        @endif
        <span class="add-schedule-btn ml-3" data-toggle="modal" data-target="#exampleModal" data-dayid="0" data-day="{{translate('sunday')}}"><i class="tio-add"></i></span>
    </div>

</div>
