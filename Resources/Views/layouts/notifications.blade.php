<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css"/>
<style>
    .select2-container {
        width: 100% !important;
        z-index: auto !important;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border: solid #d9dee7 1px;
    }

    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d9dee7;
        border-radius: 0.475rem;
        min-height: 42px;
        padding: 0.5rem 0.75rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #f1f3f6;
        border: 1px solid #d9dee7;
        border-radius: 0.25rem;
        color: #5e6278;
        padding: 0.25rem 0.5rem;
        margin: 0.125rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #7e8299;
        margin-right: 0.5rem;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #f1416c;
    }

    .select2-dropdown {
        border: 1px solid #d9dee7;
        border-radius: 0.475rem;
        box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075);
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d9dee7;
        border-radius: 0.25rem;
        padding: 0.5rem 0.75rem;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #f1f3f6;
        color: #5e6278;
    }
</style>
<!-- start model Notification -->
<div class="modal" id="modelNotification">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">{{trans('sw.send_notification_to_users')}}</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form action="{{route('sw.sendToUsers')}}" method="GET">
                    <div class="row">
                        <div class="col-12 mb-4">
                            <label class="form-label fw-bold text-gray-800">{{trans('sw.message')}}</label>
                            <textarea id="content_notification_form" name="content" class="form-control form-control-solid" 
                                      rows="4" required placeholder="{{trans('sw.message')}}" 
                                      style="resize: vertical;"></textarea>
                        </div>
                        
                        <div class="col-12 mb-4">
                            <label class="form-label fw-bold text-gray-800">{{trans('sw.select_users')}}</label>
                            <select id="select_managers" name="users[]" class="form-control form-control-solid select2-multi" 
                                    required multiple="multiple" data-placeholder="{{trans('sw.select_users')}}">
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    {{trans('sw.cancel')}}
                                </button>
                                <button class="btn btn-primary" type="submit">
                                    <i class="ki-outline ki-send me-2"></i>{{trans('sw.send_notification')}}
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End model Notification -->
<!-- start model Notification -->
<div class="modal" id="modelReadNotification">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title"><i class="fa fa-bell"></i> {{trans('sw.notification')}}</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">

{{--                    <div class="form-group">--}}
{{--                        <h3></h3>--}}
{{--                    </div><!-- end div content  -->--}}

                    <div class="form-group ">
                        <p id="readNotificationMessage"></p>
                    </div><!-- end select div -->

            </div>
        </div>
    </div>
</div>
<!-- End model Notification -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.min.js"></script>
<script>

    $('.select2-multi').select2({
        placeholder: '{{trans('sw.select_users')}}',
        allowClear: true,
        dropdownParent: $('#modelNotification'),
        width: '100%',
        theme: 'default'
    });

    $('#side_notification').off("click").on('click', function (e) {
        e.preventDefault();
        $('#content_notification_form').val('');

        $('#modelNotification').modal('show');
        $.ajax({
            url: "{{route('sw.listUserJson')}}",
            type: "get",
            success: (data) => {
                var output = '';
                for (i = 0; i < data.length; i++) {
                    // console.log('ee', data[i]);
                    output += '<option title="' + data[i]['name'] + '" value="' + data[i]['id'] + '"  >' + data[i]['name'] + ' </option>';
                }
                $('#select_managers').html(output);
                $('#select_managers').trigger('change');
                $('#modelNotification').modal('show');
            },
            error: (reject) => {
                var response = $.parseJSON(reject.responseText);
                // console.log(response);
            }


        });

        return false;
    });
</script>
<!-- end select admins-->


<script>

    // $('.main-header-notification').off('click').on('click', function (e) {
    //     e.preventDefault();
    //     var isNewNotify = '0';
    //     var notificationsWrapper = $('.main-header-notification');
    //     var notificationsPulse = notificationsWrapper.find('a span:first');
    //     notificationsPulse.removeClass('pulse');
    //     if (parseInt(isNewNotify) !== 0) {
    //         $.ajax({
    //             url: "https://demo.egym.site/ar/admin/markNotificationRead",
    //             type: "get",
    //
    //             success: (data) => {
    //                 //    console.log(data)
    //             },
    //             error: (reject) => {
    //
    //                 var response = $.parseJSON(reject.responseText);
    //                 console.log(response);
    //
    //             }
    //
    //
    //         });
    //     }
    //     return false;
    // })
</script>
<!-- end Notifications-->

<!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    function notifytone() {
        console.log('{{ asset("public/mixkit-correct-answer-reward-952.wav") }}');
        var snd = new Audio('{{ asset("public/mixkit-correct-answer-reward-952.wav") }}');
        snd.play();
    }
    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('098b6ce982918904e9e7', {
        cluster: 'eu'
    });

    var channel = pusher.subscribe('my-channel.{{$mainSettings['token']}}');
    channel.bind('my-event', function (data) {
        var getData = JSON.stringify(data);
        // alert(getData.message+ '  '+getData["user_id"]);
        var notifications_badge = $('#notifications_badge').html();
        notifications_badge = parseInt(notifications_badge) + 1;
        $('.notifications_badge').html(notifications_badge);

        $('#notifications_result').prepend('<li><a href="javascript:;" onclick="markAsRead(' + data.user_id + ', '+"'"+data.message+"'"+', '+"'"+data.url+"'"+');" class="main-header-notification">\n' +
            '                                                <span class="time">' + data.created_at + '</span>\n' +
            '                                                <span class="details">\n' +
            '                                                        <span class="label label-sm label-icon label-warning ">\n' +
            '                                                        <i class="fa fa-bell-o"></i>\n' +
            '                                                        </span>' + data.title + '</span>\n' +
            '                                            </a></li>');

        // Play notification sound immediately
        notifytone();

        // Show automatic popup notification
        var popupMsg = data.message || '';
        if (data.url && data.url !== 'null' && data.url !== '') {
            popupMsg += '<br><br><a href="' + data.url + '" style="color:#4e73df;font-weight:bold;">{{ trans("sw.press_here") }}</a>';
        }
        Swal.fire({
            title: '<i class="fa fa-bell" style="color:#f6c23e;margin-left:6px;"></i> ' + (data.title || '{{ trans("sw.new_notification") }}'),
            html: popupMsg,
            icon: 'info',
            position: 'top-end',
            showConfirmButton: true,
            confirmButtonText: '{{ trans("sw.ok") }}',
            confirmButtonColor: '#4e73df',
            timer: 8000,
            timerProgressBar: true,
            toast: false,
        });
    });
    // Some useful debug msgs
    // pusher.connection.bind('connecting', function() {
    //     alert('Connecting to Pusher...');
    // });
    // pusher.connection.bind('connected', function() {
    //     alert('Connected to Pusher!');
    // });
    // pusher.connection.bind('failed', function() {
    //     alert('Connection to Pusher failed :(');
    // });
    // myChannel.bind('subscription_error', function(status) {
    //     alert('Pusher subscription_error');
    // });
</script>
<script>
    function markAsRead(id, message, url = null) {

        $.ajax({
            url: "{{route('sw.markAsRead')}}",
            data: {id: id},
            type: "get",
            success: (data) => {
                if(url && url != 'null'){message = message + '<br/><br/>' + '<a href="'+url+'">'+'{{trans('sw.press_here')}}'+'</a>'}
                $('#readNotificationMessage').html(message);
                $('#modelReadNotification').modal('show');

                var notifications_badge = $('#notifications_badge').html();
                notifications_badge = parseInt(notifications_badge) - 1;
                $('.notifications_badge').html(notifications_badge);

                $('#notification_li_'+id).remove();
            },
            error: (reject) => {
                var response = $.parseJSON(reject.responseText);
                console.log(response);
            }

        });
    }
</script>


