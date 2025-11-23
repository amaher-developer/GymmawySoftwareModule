@if($swUser->is_super_user)
<style>
    .maher-img-backup {
        margin-top: 20px;
        margin-bottom: 20px;
        border: 1px gray solid;
    }
</style>
<!-- start model Barcode -->
<div class="modal" id="modelBackup">
    <div class="modal-dialog" role="document">
        <div class="modal-content modal-content-demo">
            <div class="modal-header">
                <h6 class="modal-title">{{trans('sw.backup_msg')}}</h6>
                <button aria-label="Close" class="close" data-dismiss="modal" type="button"><span
                            aria-hidden="true">&times;</span></button>
            </div>


            <div class="clearfix"></div>

            <div class="row" style="margin: 0">
                <div class="col-md-12 alert alert-info"><i class="fa fa-info-circle"></i> {{trans('sw.info_internet_connection')}}</div>
                <div class="col-md-12 alert alert-info"><i class="fa fa-info-circle"></i> {!! trans('sw.info_get_account') !!}</div>
            </div>

            <div class="clearfix"></div>
            <div class="row">
                <div class="col-md-12 text-center">
                    <img alt="user-img" class="maher-img-backup text-center avatar-xl brround mCS_img_loaded" src="{{asset('resources/assets/new_front/img/cloud-database.webp')}}" loading="lazy">
                </div>
            </div>
            <div class="clearfix"></div>

            <div class="modal-body" id="db_modal_body">

                <div id="backup_result"></div>
{{--                    <div class="form-group">--}}
{{--                        <label>{{trans('sw.qty')}}</label>--}}
{{--                        <input name="qty" class="form-control" min="0" max="50" type="number" required--}}
{{--                               placeholder="{{trans('sw.qty')}}">--}}
{{--                    </div><!-- end div qty  -->--}}
                <div id="db_form_group">
                    <div class="form-group" >
                        <!--ie8, ie9 does not support html5 placeholder, so we just show field title for that-->
                        <label class="control-label visible-ie8 visible-ie9">{{trans('sw.username')}}</label>
                        <div class="input-icon">
                            <i class="fa fa-user"></i>
                            <input class="form-control placeholder-no-fix" dir="ltr" type="text" autocomplete="off" placeholder="{{trans('sw.email')}}" id="backup_email"/>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label visible-ie8 visible-ie9">{{trans('sw.password')}}</label>
                        <div class="input-icon">
                            <i class="fa fa-lock"></i>
                            <input class="form-control placeholder-no-fix" dir="ltr" type="password" autocomplete="off" placeholder="{{trans('sw.password')}}" id="backup_password"/>
                        </div>
                    </div>
                    <button class="btn ripple btn-primary"  onclick="backupSubmit();return false;"
                            type="button">{{trans('global.submit')}}</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End model Barcode -->
<script>

    function backupSubmit() {
        email = $('#backup_email').val();
        password = $('#backup_password').val();
        if(!email || !password ){
            document.getElementById('backup_result').innerHTML = '<div class="alert alert-danger">{{trans('sw.error_login')}}</div>';
        }else{
            $('<div id="loading_div" align="center" style="width: 100%;background-position: center;padding-top: 50px;"><img id="loading" src="{{asset('resources/assets/new_front/img/loading1.gif')}}" loading="lazy" /></div>').insertAfter('#backup_result');
            $('#db_form_group').hide();
            $.ajax({
                url: "{{route('sw.backupDB')}}",
                Method:'POST',
                data: {"_token": "{{csrf_token()}}", "email": email, "password": password},
                success: (data) => {
                    // if(data.slice(-1) == '0'){
                    if(data.charAt(0) == '0'){
                        document.getElementById('backup_result').innerHTML = '<div class="alert alert-danger">{{trans('sw.error_backup')}}</div>';
                    }
                    // if(data.slice(-1) == '1'){
                    if(data.charAt(0) == '1'){

                        $('#modelBackup').modal('hide');
                        $('#backup_email').val('');
                        $('#backup_password').val('');
                        document.getElementById('backup_result').innerHTML = '';
                        swal({
                            title: "{{trans('admin.done')}}",
                            text: "{{trans('admin.successfully_processed')}}",
                            type: "success",
                            timer: 4000,
                            confirmButtonText: 'Ok',
                        });
                    }
                    $('#db_form_group').show();
                    $('#loading').remove();
                    $('#loading_div').remove();
                },
                error: (reject) => {
                    $('#modelBackup').modal('hide');
                    $('#backup_email').val('');
                    $('#backup_password').val('');
                    swal({
                        title: "{{trans('admin.operation_failed')}}",
                        text: "{{trans('admin.operation_failed')}}",
                        type: "error",
                        timer: 4000,
                        confirmButtonText: 'Ok',
                    });
                    var response = $.parseJSON(reject.responseText);

                    $('#db_form_group').show();
                    $('#loading').remove();
                    $('#loading_div').remove();
                }


            });
        }

        return false;
    }
</script>
@endif


