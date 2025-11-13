@extends('software::layouts.list')
@section('list_title') {{ @$title }} @endsection
@section('breadcrumb')
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.dashboard') }}" class="text-muted text-hover-primary">{{ trans('sw.home')}}</a>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-300 w-5px h-2px"></span>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item text-gray-900">{{ $title }}</li>
        <!--end::Item-->
    </ul>
    <!--end::Breadcrumb-->
@endsection
@section('styles')
    <link rel="stylesheet" type="text/css" href="{{asset('/')}}resources/assets/admin/global/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css"/>
<style>
    .setting-images {
        height: 200px;
        max-width: 200px;
        object-fit: cover;
    }
    </style>
    @endsection
@section('page_body')

<!--begin::Gallery-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-picture fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.gallery')}}</span>
            </div>
        </div>
        <!--end::Card title-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Upload Image-->
                @if(in_array('editSettingUploadImage', (array)$swUser->permissions) || $swUser->is_super_user)
                    <label class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('sw.upload_image')}}
                        <input type="file" name="file" id="upload_file" class="d-none" accept="image/*"/>
                    </label>
                @endif
                <!--end::Upload Image-->
            </div>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--end::Card header-->

    <!--begin::Card body-->
    <div class="card-body pt-0">
        <!--begin::Search-->
        <div class="d-flex align-items-center position-relative my-1 mb-5">
            <i class="ki-outline ki-magnifier fs-3 position-absolute ms-4"></i>
            <form class="d-flex" action="" method="get" style="max-width: 400px;">
                <input type="text" name="search" class="form-control form-control-solid ps-12" value="{{ request('search') }}" placeholder="{{ trans('sw.search_on')}}">
                <button class="btn btn-primary" type="submit">
                    <i class="ki-outline ki-magnifier fs-3"></i>
                </button>
            </form>
        </div>
        <!--end::Search-->

        <!--begin::Total count-->
        <div class="d-flex align-items-center mb-5">
            <div class="symbol symbol-50px me-5">
                <div class="symbol-label bg-light-primary">
                    <i class="ki-outline ki-chart-simple fs-2x text-primary"></i>
                </div>
            </div>
            <div class="d-flex flex-column">
                <span class="fs-6 fw-semibold text-gray-900">{{ trans('admin.total_count')}}</span>
                <span class="fs-2 fw-bold text-primary">{{ count($mainSettings->images ?? []) }}</span>
            </div>
        </div>
        <!--end::Total count-->

        @if(count($mainSettings->images ?? []) > 0)
            <!--begin::Gallery Grid-->
            <div class="row g-5" id="uploaded_image">
                @foreach($mainSettings->images as $index => $image)
                    <div class="col-lg-3 col-md-4 col-sm-6" id="div_upload_image_{{$index}}">
                        <div class="card card-flush h-100">
                            <div class="card-body p-0">
                                <!--begin::Image-->
                                <div class="position-relative">
                                    <img src="{{$imagePath.'/'.$image}}" 
                                         class="card-img-top w-100" 
                                         style="height: 200px; object-fit: cover;" 
                                         alt="Gallery Image" />
                                    <!--begin::Overlay-->
                                    <div class="position-absolute top-0 end-0 p-3">
                                        @if(in_array('editSettingDeleteUploadImage', (array)$swUser->permissions) || $swUser->is_super_user)
                                            <button type="button" 
                                                    class="btn btn-icon btn-sm btn-light-danger" 
                                                    onclick="deleteUploadImage('{{$image}}')" 
                                                    title="{{ trans('admin.delete')}}">
                                                <i class="ki-outline ki-trash fs-2"></i>
                                            </button>
                                        @endif
                                    </div>
                                    <!--end::Overlay-->
                                </div>
                                <!--end::Image-->
                                <!--begin::Content-->
                                <div class="card-body">
                                    <div class="text-gray-800 fs-6 fw-bold">
                                        {{ trans('sw.image')}} {{ $index + 1 }}
                                    </div>
                                    <div class="text-muted fs-7">
                                        {{ $image }}
                                    </div>
                                </div>
                                <!--end::Content-->
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <!--end::Gallery Grid-->
        @else
            <!--begin::Empty State-->
            <div class="text-center py-10">
                <div class="symbol symbol-100px mb-5">
                    <div class="symbol-label fs-2x fw-semibold text-success bg-light-success">
                        <i class="ki-outline ki-picture fs-2"></i>
                    </div>
                </div>
                <h4 class="text-gray-800 fw-bold">{{ trans('sw.no_images_found')}}</h4>
            </div>
            <!--end::Empty State-->
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Gallery-->

    <style>
        .more {display: none;}
    </style>


@endsection

@section('scripts')
 <script>
     function deleteUploadImage(image){

         var confirmText = "{{ trans('admin.are_you_sure')}}";
         if(image && confirm(confirmText)){
             var form_data = new FormData();
             form_data.append("image", image);
             form_data.append("_token", "{{ csrf_token() }}");
             $.ajax({
                 url: "{{ route('sw.editSettingDeleteUploadImage') }}",
                 method: "POST",
                 data: form_data,
                 contentType: false,
                 cache: false,
                 processData: false,
                 success: function (data) {
                     if (data == 'false') {
                         alert("{{ trans('sw.image_upload_error_msg')}}");
                     } else {
                         $('#div_upload_image_'+data).remove();
                     }
                 }
             });
         }

         return false;
     }


     $("#upload_file").change(function () {
         // $(document).on('change', '#upload_file', function () {
         var name = document.getElementById("upload_file").files[0].name;
         var form_data = new FormData();
         var ext = name.split('.').pop().toLowerCase();
         if (jQuery.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
             alert("Invalid Image File");
         }
         var oFReader = new FileReader();
         oFReader.readAsDataURL(document.getElementById("upload_file").files[0]);
         var f = document.getElementById("upload_file").files[0];
         var fsize = f.size || f.fileSize;
         if (fsize > 2000000) {
             alert("{{ trans('sw.image_size_msg')}}");
         } else {
             form_data.append("file", document.getElementById('upload_file').files[0]);
             form_data.append("_token", "{{ csrf_token() }}");
             $.ajax({
                 url: "{{ route('sw.editSettingUploadImage') }}",
                 method: "POST",
                 data: form_data,
                 contentType: false,
                 cache: false,
                 processData: false,
                 beforeSend: function () {

                     $('#uploaded_image').hide();
                     $('#uploaded_image').after("<label class='text-success' id='loading'>Image Uploading...</label>");
                 },
                 success: function (data) {
                     if (data == '0') {
                         alert("{{ trans('sw.image_upload_error_msg')}}");
                     } else if (data == '1') {
                         alert("{{ trans('sw.image_max_error_msg')}}");
                     } else {
                         var index = data.lastIndexOf("/") + 1;
                         var filename = data.substr(index);
                         $('#uploaded_image').append('<img src="' + data + '" height="150" width="225" class="img-thumbnail  setting-images" />'
                             // +'<br/><span><a href="javascript:void(0)" onclick="deleteUploadImage("'+filename+'");"><i class="fa fa-trash"></i></a></span>'
                         );
                     }

                     $('#loading').remove();
                     $('#uploaded_image').show();
                 }
             });

         }
     });
 </script>

@endsection
