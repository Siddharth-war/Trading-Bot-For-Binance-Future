<div class="">
    <div class="media align-items-center gap-3 border rounded p-2 mb-3">
        <div class="avatar">
            <img class="img-fit rounded-circle" src="{{$user->imageFullPath}}" alt="{{ translate('user') }}">
        </div>
        <div>
            <h5 class="mb-0 media-body">{{$user['f_name'].' '.$user['l_name']}}</h5>
            <span class="fz-12">{{$user['phone']}}</span>
        </div>
    </div>

    <div class="chat_conversation">
        <div class="chat_conversation-inner">
            @foreach($convs as $key=>$con)
                @if(($con->message!=null && $con->reply==null) || $con->is_reply == false)
                    <div class="received_msg">
                        @if(isset($con->message))
                            <div class="msg">{{$con->message}}</div>
                            <span class="time_date"> {{\Carbon\Carbon::parse($con->created_at)->format('h:m a | M d')}}</span>
                        @endif
                        <?php try {?>

    {{--                        @if($con->image != null && $con->image != "null" && count(json_decode($con->image, true)) > 0)--}}
                        @if($con->image != null && $con->image != "null")
                                <div class="row gx-2 mt-2">
                                @php($image_array = json_decode($con->image, true))
                                @foreach($image_array as $image)
                                    <a href="{{$image}}" data-lightbox="{{$con->id . $image}}" >
                                        <img class="rounded" src="{{$image}}" onerror="this.src='{{asset('public/assets/admin/img/900x400/img1.jpg')}}'" />
                                    </a><br>
                               @endforeach
                                </div>
                                <span class="time_date">  {{\Carbon\Carbon::parse($con->created_at)->format('h:m a | M d')}}</span>
                            @endif
                        <?php }catch (\Exception $e) {
                        } ?>
                    </div>
                @endif
                @if(($con->reply!=null && $con->message==null) || $con->is_reply == true)
                    <div class="outgoing_msg">
                        @if(isset($con->reply))
                            <div class="msg">{{$con->reply}}</div>
                            <span class="time_date">  {{\Carbon\Carbon::parse($con->created_at)->format('h:m a | M d')}}</span>
                        @endif
                        <?php try {?>
    {{--                            @if($con->image != null && $con->image != "null" && count(json_decode($con->image, true)) > 0)--}}
                            @if($con->image != null && $con->image != "null")
                                <div class="row g-2 mt-2">
                                @php($image_array = json_decode($con->image, true))
                                @foreach($image_array as $key=>$image)
                                    @php($image_url = $image)
                                    <div class="col-12 @if(count(json_decode($con->image, true)) > 1) col-md-6 @endif">
                                        <a href="{{asset('storage/app/public/conversation').'/'.$image_url}}" data-lightbox="{{$con->id . $image_url }}" >
                                            <img class="rounded" src="{{asset('storage/app/public/conversation').'/'.$image_url}}" onerror="this.src='{{asset('public/assets/admin/img/900x400/img1.jpg')}}'" />
                                        </a><br>
                                    </div>
                                @endforeach
                                </div>
                                <span class="time_date">  {{\Carbon\Carbon::parse($con->created_at)->format('h:m a | M d')}}</span>
                            @endif
                        <?php }catch (\Exception $e) {} ?>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
<form action="javascript:" method="post" id="reply-form">
    @csrf
    <div class="card mt-2">
        <div class="p-2">
            <div class="quill-custom_">
                <textarea class="border-0 w-100" name="reply"></textarea>
            </div>

            <div id="accordion" class="d-flex gap-2 justify-content-end">
                <button class="btn btn-primary btn-sm" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    {{translate('Upload')}}
                    <i class="tio-upload"></i>
                </button>
                <button type="submit" data-url="{{route('admin.message.store',[$user->id])}}" class="btn btn-sm btn-primary reply-message">
                        {{translate('send')}}
                        <i class="tio-send"></i>
                </button>
            </div>

            <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo"
                 data-parent="#accordion">
                <div class="row mt-3" id="coba"></div>
            </div>
        </div>
    </div>
</form>

<script src="{{asset('public/assets/admin')}}/js/tags-input.min.js"></script>
<script src="{{asset('public/assets/admin/js/spartan-multi-image-picker.js')}}"></script>

<script>
    "use strict";

    $('.reply-message').click(function() {
        var url = $(this).data('url');
        replyConvs(url);
    });

    $(document).ready(function () {
        $('.chat_conversation').animate({
            scrollTop: $('.chat_conversation-inner').height()
        }, 500);
    });

    $('#collapseTwo').on('show.bs.collapse', function () {
        spartanMultiImagePicker();
    })

    $('#collapseTwo').on('hidden.bs.collapse', function () {
        document.querySelector("#coba").innerHTML = "";
    })

    function spartanMultiImagePicker() {
        document.querySelector("#coba").innerHTML = "";

        $("#coba").spartanMultiImagePicker({
            fieldName: 'images[]',
            maxCount: 4,
            rowHeight: '10%',
            groupClassName: 'col-lg-3 col-md-4 col-6',
            maxFileSize: '',
            dropFileLabel: "Drop Here",
            onAddRow: function (index, file) {

            },
            onRenderedPreview: function (index) {

            },
            onRemoveRow: function (index) {

            },
            onExtensionErr: function (index, file) {
                toastr.error('{{translate('Please only input png or jpg type file')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onSizeErr: function (index, file) {
                toastr.error('{{translate('File size too big')}}', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        });
    }
</script>
