<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;
use Modules\Software\Classes\TypeConstants;
use Modules\Software\Http\Requests\GymUserPermissionRequest;
use Modules\Software\Models\GymUserPermission;
use Modules\Software\Repositories\GymUserPermissionRepository;
use Carbon\Carbon;
use Illuminate\Container\Container as Application;

class GymUserPermissionFrontController extends GymGenericFrontController
{
    public $PermissionRepository;

    public function __construct()
    {
        parent::__construct();
        $this->PermissionRepository = new GymUserPermissionRepository(new Application);
        // Repository branch filtering removed from constructor - now applied per query
    }

    public function index()
    {
        $title = trans('sw.permission_groups');
        $this->request_array = ['search'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        
        if(request('trashed'))
        {
            $permissions = $this->PermissionRepository->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $permissions = $this->PermissionRepository->orderBy('id', 'DESC');
        }

        //apply filters
        $permissions->when($search, function ($query) use ($search) {
            $query->where(function($query) use ($search) {
                $query->where('id', '=', $search);
                $query->orWhere('title_' . $this->lang, 'like', "%" . $search . "%");
            });
        });
        $search_query = request()->query();

        if ($this->limit) {
            $permissions = $permissions->paginate($this->limit);
            $total = $permissions->total();
        } else {
            $permissions = $permissions->get();
            $total = $permissions->count();
        }
        return view('software::Front.user_permission_front_list', compact('permissions','title', 'total', 'search_query'));
    }

    public function create()
    {
        $title = trans('sw.permission_group_add');
        return view('software::Front.user_permission_front_form', ['permission_group' => new GymUserPermission(),'title'=>$title]);
    }

    public function store(GymUserPermissionRequest $request)
    {
        $inputs = $this->prepare_inputs($request->except(['_token']));
        // Handle text fields - convert null/empty values to empty strings to avoid null constraint violations
        if (isset($inputs['content_ar'])) {
            $inputs['content_ar'] = $inputs['content_ar'] !== null ? $inputs['content_ar'] : '';
        } else {
            $inputs['content_ar'] = '';
        }
        if (isset($inputs['content_en'])) {
            $inputs['content_en'] = $inputs['content_en'] !== null ? $inputs['content_en'] : '';
        } else {
            $inputs['content_en'] = '';
        }


        if(@$this->user_sw->branch_setting_id){
            $inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
            $inputs['tenant_id'] = @$this->user_sw->tenant_id;
        }
        if(@$this->user_sw->id){
            $inputs['user_id'] = @$this->user_sw->id;
        }
        
        $this->PermissionRepository->create($inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);

        $notes = str_replace(':name', $inputs['title_'.$this->lang], trans('sw.add_permission_group'));
        $this->userLog($notes, TypeConstants::CreatePermissionGroup);
        return redirect(route('sw.listUserPermission'));
    }

    public function edit($id)
    {
        $permission_group = $this->PermissionRepository->withTrashed()->find($id);
        $title = trans('sw.permission_group_edit');
        return view('software::Front.user_permission_front_form', ['permission_group' => $permission_group,'title'=>$title]);
    }

    public function update(GymUserPermissionRequest $request, $id)
    {
        $permission_group = $this->PermissionRepository->withTrashed()->find($id);
        $inputs = array_filter($this->prepare_inputs($request->except(['_token'])));
             
        $permission_group->update($inputs);
        
        // Update all users using this permission group
        $this->updateUsersPermissions($id, $request->input('permissions', []));

        $notes = str_replace(':name', $permission_group['title'], trans('sw.edit_permission_group'));
        $this->userLog($notes, TypeConstants::EditPermissionGroup);

        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listUserPermission'));
    }

    public function destroy($id)
    {
        $permission_group = $this->PermissionRepository->withTrashed()->find($id);
        if($permission_group->trashed())
        {
            $permission_group->restore();
        }
        else
        {
            $permission_group->delete();

            $notes = str_replace(':name', $permission_group['title'], trans('sw.delete_permission_group'));
            $this->userLog($notes, TypeConstants::DeletePermissionGroup);
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('sw.listUserPermission'));
    }


    private function prepare_inputs($inputs)
    {
        // Handle text fields - convert null/empty values to empty strings to avoid null constraint violations
        if (isset($inputs['content_ar'])) {
            $inputs['content_ar'] = $inputs['content_ar'] !== null ? $inputs['content_ar'] : '';
        } else {
            $inputs['content_ar'] = '';
        }
        if (isset($inputs['content_en'])) {
            $inputs['content_en'] = $inputs['content_en'] !== null ? $inputs['content_en'] : '';
        } else {
            $inputs['content_en'] = '';
        }


        if(@$this->user_sw->branch_setting_id){
            $inputs['branch_setting_id'] = @$this->user_sw->branch_setting_id;
            $inputs['tenant_id'] = @$this->user_sw->tenant_id;
        }
//        !$inputs['deleted_at']?$inputs['deleted_at']=null:'';

        return $inputs;
    }
    
    /**
     * Update permissions for all users using this permission group
     * 
     * @param int $permission_group_id
     * @param array $permissions
     * @return void
     */
    private function updateUsersPermissions($permission_group_id, $permissions)
    {
        // Get all users using this permission group
        $users = \Modules\Software\Models\GymUser::branch($this->user_sw->branch_setting_id, @$this->user_sw->tenant_id)->where('permission_group_id', $permission_group_id)->get();
        
        // Update each user's permissions
        foreach($users as $user){
            $user->permissions = $permissions;
            $user->save();
        }
        
        // Log the update
        if($users->count() > 0){
            $notes = trans('sw.updated_permissions_for_users', ['count' => $users->count()]);
            $this->userLog($notes, TypeConstants::EditPermissionGroup);
        }
    }
}


