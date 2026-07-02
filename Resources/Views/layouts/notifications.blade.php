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
<style>
    @keyframes bell-shake {
        0%,100% { transform: rotate(0deg); }
        10%      { transform: rotate(-18deg); }
        20%      { transform: rotate(18deg); }
        30%      { transform: rotate(-14deg); }
        40%      { transform: rotate(14deg); }
        50%      { transform: rotate(-10deg); }
        60%      { transform: rotate(10deg); }
        70%      { transform: rotate(-6deg); }
        80%      { transform: rotate(6deg); }
        90%      { transform: rotate(-2deg); }
    }
    .bell-ringing {
        display: inline-block;
        animation: bell-shake 0.9s ease both;
        transform-origin: top center;
    }
</style>

<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    if (typeof window.notifytone === 'undefined') {
        window.notifytone = function notifytone() {
            var snd = new Audio('{{ asset("mixkit-correct-answer-reward-952.wav") }}');
            snd.play().catch(function() {});
        };
    }

    function ringBell() {
        var bell = document.getElementById('notification_bell_icon');
        if (!bell) return;
        bell.classList.remove('bell-ringing');
        // force reflow so the animation restarts if already running
        void bell.offsetWidth;
        bell.classList.add('bell-ringing');
        setTimeout(function() { bell.classList.remove('bell-ringing'); }, 900);
    }

    Pusher.logToConsole = false;

    var pusher = new Pusher('098b6ce982918904e9e7', {
        cluster: 'eu'
    });

    var channel = pusher.subscribe('my-channel.{{$mainSettings['token']}}');
    channel.bind('my-event', function (data) {
        // Update badge count
        var notifications_badge = parseInt($('#notifications_badge').html()) || 0;
        $('.notifications_badge').html(notifications_badge + 1);

        // Add to dropdown list
        $('#notifications_result').prepend(
            '<li><a href="javascript:;" onclick="markAsRead(' + data.user_id + ', \'' + data.message + '\', \'' + data.url + '\');" class="main-header-notification">' +
            '<span class="time">' + (data.created_at || '') + '</span>' +
            '<span class="details"><span class="label label-sm label-icon label-warning"><i class="fa fa-bell-o"></i></span>' + (data.title || '') + '</span>' +
            '</a></li>'
        );

        // 1. Play sound
        notifytone();

        // 2. Shake the bell icon
        ringBell();

        // 3. Toast popup in top-end corner
        var popupMsg = data.message || '';
        if (data.url && data.url !== 'null' && data.url !== '') {
            popupMsg += '<br><a href="' + data.url + '" style="color:#fff;font-weight:bold;text-decoration:underline;">{{ trans("sw.press_here") }}</a>';
        }
        Swal.fire({
            title: (data.title || '{{ trans("sw.new_notification") }}'),
            html: popupMsg,
            icon: 'info',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 8000,
            timerProgressBar: true,
            didOpen: function(toast) {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
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


