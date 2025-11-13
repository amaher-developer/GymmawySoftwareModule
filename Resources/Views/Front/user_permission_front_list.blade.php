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
        <li class="breadcrumb-item text-muted">
            <a href="{{ route('sw.listUser') }}" class="text-muted text-hover-primary">{{ trans('sw.users')}}</a>
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
@section('page_body')

<!--begin::Permission Groups-->
<div class="card card-flush">
    <!--begin::Card header-->
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <!--begin::Card title-->
        <div class="card-title">
            <div class="d-flex align-items-center my-1">
                <i class="ki-outline ki-security-user fs-2 me-3"></i>
                <span class="fs-4 fw-semibold text-gray-900">{{ trans('sw.permission_groups')}}</span>
            </div>
        </div>
        <!--end::Card title-->
        <div class="card-toolbar">
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <!--begin::Add Permission Group-->
                @if(in_array('createUserPermission', (array)$swUser->permissions) || $swUser->is_super_user)
                    <a href="{{route('sw.createUserPermission')}}" class="btn btn-sm btn-flex btn-light-primary">
                        <i class="ki-outline ki-plus fs-6"></i>
                        {{ trans('admin.add')}}
                    </a>
                @endif
                <!--end::Add Permission Group-->
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
                <span class="fs-2 fw-bold text-primary">{{ $total }}</span>
            </div>
        </div>
        <!--end::Total count-->

        @if(count($permissions) > 0)
            <!--begin::Table-->
            <div class="table-responsive">
                <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="min-w-200px text-nowrap">
                            <i class="ki-outline ki-security-user fs-6 me-2"></i>{{ trans('sw.title')}}
                        </th>
                        <th class="min-w-150px text-nowrap">
                            <i class="ki-outline ki-shield-tick fs-6 me-2"></i>{{ trans('sw.permissions_count')}}
                        </th>
                        <th class="min-w-150px text-nowrap">
                            <i class="ki-outline ki-people fs-6 me-2"></i>{{ trans('sw.users_count')}}
                        </th>
                        <th class="text-end min-w-70px text-nowrap">
                            <i class="ki-outline ki-setting-2 fs-6 me-2"></i>{{ trans('admin.actions')}}
                        </th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @foreach($permissions as $permission)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-50px me-3">
                                        <div class="symbol-label fs-3 bg-light-primary text-primary">
                                            <i class="ki-outline ki-shield-tick fs-2"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-gray-800 text-hover-primary fs-5 fw-bold">
                                            {{ $permission->title }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="pe-0">
                                <span class="badge badge-light-primary fs-7">
                                    {{ is_array($permission->permissions) ? count($permission->permissions) : 0 }} {{ trans('sw.permissions')}}
                                </span>
                            </td>
                            <td class="pe-0">
                                <span class="badge badge-light-success fs-7">
                                    {{ $permission->users->count() }} {{ trans('sw.users')}}
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-1">
                                    @if(in_array('editUserPermission', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <a href="{{route('sw.editUserPermission',$permission->id)}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" title="{{ trans('admin.edit')}}">
                                            <i class="ki-outline ki-pencil fs-2"></i>
                                        </a>
                                    @endif
                                    
                                    @if(in_array('deleteUserPermission', (array)$swUser->permissions) || $swUser->is_super_user)
                                        <a href="{{route('sw.deleteUserPermission',$permission->id)}}"
                                           class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm js-permission-delete"
                                           data-confirm-title="{{ trans('sw.are_you_sure') }}"
                                           data-confirm-text="{{ trans('sw.delete_permission_group', ['name' => $permission->title]) }}"
                                           title="{{ trans('admin.delete')}}">
                                            <i class="ki-outline ki-trash fs-2"></i>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            <!--end::Table-->
            
            <!--begin::Pagination-->
            @if(method_exists($permissions, 'links'))
                <div class="d-flex justify-content-center">
                    {{ $permissions->appends($search_query) }}
                </div>
            @endif
            <!--end::Pagination-->
        @else
            <div class="text-center py-10">
                <i class="ki-outline ki-security-user fs-5x text-gray-400"></i>
                <p class="text-gray-600 fs-4 fw-semibold mt-5">{{ trans('sw.no_data')}}</p>
            </div>
        @endif
    </div>
    <!--end::Card body-->
</div>
<!--end::Permission Groups-->

@endsection

@section('scripts')
    @parent
    <script>
        (function () {
            function initPermissionDeleteConfirm() {
                var deleteButtons = document.querySelectorAll('.js-permission-delete');
                if (!deleteButtons.length) {
                    return;
                }
                deleteButtons.forEach(function (button) {
                    if (button.dataset.confirmBound === 'true') {
                        return;
                    }
                    button.dataset.confirmBound = 'true';

                    button.addEventListener('click', function (event) {
                        event.preventDefault();
                        var url = button.getAttribute('href');
                        if (!url) {
                            return;
                        }
                        var title = button.getAttribute('data-confirm-title') || '{{ trans('sw.are_you_sure') }}';
                        var text = button.getAttribute('data-confirm-text') || '{{ trans('sw.delete_permission_group', ['name' => '']) }}';

                        var options = {
                            title: title,
                            text: text,
                            type: "warning",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "{{ trans('sw.yes') }}",
                            cancelButtonText: "{{ trans('sw.no') }}"
                        };

                        if (window.Swal && typeof window.Swal.fire === 'function') {
                            window.Swal.fire(options).then(function (result) {
                                if (result && (result.isConfirmed || result.value === true)) {
                                    window.location.href = url;
                                }
                            });
                        } else if (typeof swal === 'function') {
                            if (typeof swal.fire === 'function') {
                                swal.fire(options).then(function (result) {
                                    if (result && (result.isConfirmed || result.value === true)) {
                                        window.location.href = url;
                                    }
                                });
                            } else {
                                var promiseLike;
                                try {
                                    promiseLike = swal(options);
                                } catch (error) {
                                    promiseLike = null;
                                }

                                if (promiseLike && typeof promiseLike.then === 'function') {
                                    promiseLike.then(function (result) {
                                        // SweetAlert 2 style promise
                                        if (result && (result.isConfirmed || result.value === true)) {
                                            window.location.href = url;
                                        }
                                    });
                                } else {
                                    // SweetAlert (original) callback style
                                    swal(options, function (isConfirm) {
                                        if (isConfirm) {
                                            window.location.href = url;
                                        }
                                    });
                                }
                            }
                        } else if (window.confirm(text)) {
                            window.location.href = url;
                        }
                    });
                });
            }

            window.initPermissionDeleteConfirm = initPermissionDeleteConfirm;

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initPermissionDeleteConfirm);
            } else {
                initPermissionDeleteConfirm();
            }
        })();
    </script>
@endsection
