<div class="border-bottom"></div>
@php($array=[])
@foreach($conversations as $conv)
    @if(in_array($conv->user_id,$array)==false)
        @php(array_push($array,$conv->user_id))
        @php($user=\App\User::find($conv->user_id))
        @php($unchecked=\App\Model\Conversation::where(['user_id'=>$conv->user_id,'checked'=>0])->count())
        <div class="sidebar_primary_div d-flex border-bottom pb-2 pt-2 pl-md-1 pl-0 justify-content-between align-items-center customer-list view-convs {{$unchecked!=0?'conv-active':''}}"
            data-url="{{route('admin.message.view',[$conv->user_id])}}" data-id="customer-{{$conv->user_id}}')"
            id="customer-{{$conv->user_id}}">
            <div class="avatar avatar-lg avatar-circle">
                <img class="avatar-img custom-avatar-img" src="{{$user->imageFullPath}}" alt="{{ translate('image') }}">
            </div>
            <h5 class="sidebar_name mb-0 mr-3 d-none d-md-block">
                {{$user['f_name'].' '.$user['l_name']}} <span
                    class="{{$unchecked!=0?'badge badge-info':''}}" id="counter-{{$conv->user_id}}">{{$unchecked!=0?$unchecked:''}}</span>
            </h5>
        </div>
    @endif
@endforeach
<script>
    "use strict";

    $('.view-convs').click(function() {
        var url = $(this).data('url');
        var id = $(this).data('id');
        viewConvs(url,id);
    });
</script>
