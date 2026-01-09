<?php

namespace Modules\Software\Http\Controllers\Front;

use Modules\Generic\Http\Controllers\Front\GenericFrontController;

use Modules\Software\Repositories\GymMemberSystemRepository;
use Illuminate\Container\Container as Application;
use Modules\Software\Http\Requests\GymMemberSystemRequest;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class GymMemberSystemFrontController extends GymGenericFrontController
{
    public $GymMemberSystemRepository;
    private $imageManager;

    public function __construct()
    {
        parent::__construct();
        $this->imageManager = new ImageManager(new Driver());

        $this->GymMemberSystemRepository = new GymMemberSystemRepository(new Application);
        // Repository branch filtering removed from constructor - now applied per query
    }


    public function index()
    {

        $title = trans('global.members');
            $members = $this->GymMemberSystemRepository->where('gym_id', @$this->current_gym_id)->orderBy('id', 'DESC');
            $total = $members->count();

        return view('software::Front.user.member_front_list', compact('title', 'total'));
    }


    public function create()
    {
        $title = trans('global.member_add');
        return view('software::Front.user.member_front_form', ['member' => new Member(),'title'=>$title]);
    }

    public function store(GymMemberSystemRequest $request)
    {
        $member_inputs = $this->prepare_inputs($request->except(['_token']));
        $member_inputs['gym_id'] = @$this->current_gym_id;
        $member = $this->GymMemberSystemRepository->updateOrCreate(['phone' => $member_inputs['phone']], $member_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_added'),
            'type' => 'success'
        ]);
        return redirect(route('createUserGymOrder', $member->id));
    }

    public function edit($id)
    {
        $member =$this->GymMemberSystemRepository->withTrashed()->find($id);
        $title = trans('global.member_edit');
        return view('software::Front.user.member_front_form', ['member' => $member,'title'=>$title]);
    }

    public function update(GymMemberSystemRequest $request, $id)
    {
        $member =$this->GymMemberSystemRepository->withTrashed()->find($id);
        $member_inputs = $this->prepare_inputs($request->except(['_token']));
        $member->update($member_inputs);
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_edited'),
            'type' => 'success'
        ]);
        return redirect(route('listUserMember'));
    }

    public function destroy($id)
    {
        $member =$this->GymMemberSystemRepository->withTrashed()->find($id);
        if($member->trashed())
        {
            $member->restore();
        }
        else
        {
            $member->delete();
        }
        session()->flash('sweet_flash_message', [
            'title' => trans('admin.done'),
            'message' => trans('admin.successfully_deleted'),
            'type' => 'success'
        ]);
        return redirect(route('listUserMember'));
    }


    public function showAll()
    {
        $members = $this->GymMemberSystemRepository
            ->where('gym_id', $this->current_gym_id);
        if(request()->get('trashed') == 1)
        {
            $members = $members->onlyTrashed();
        }
        $ret['data'] = $members->orderBy('id', 'DESC')->get()->toArray();
        return $ret;

    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

        $destinationPath = base_path(Member::$uploads_path);
        $ThumbnailsDestinationPath = base_path(Member::$thumbnails_uploads_path);

        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, $mode = 0777, true, true);
        }
        if (!File::exists($ThumbnailsDestinationPath)) {
            File::makeDirectory($ThumbnailsDestinationPath, $mode = 0777, true, true);
        }
        if (request()->hasFile($input_file)) {
            $file = request()->file($input_file);

            if (file_exists($file->getRealPath()) && getimagesize($file->getRealPath()) !== false) {
                $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();


                $uploaded = $filename;

                $img = $this->imageManager->read($file);
                $original_width = $img->width();
                $original_height = $img->height();

                if ($original_width > 1200 || $original_height > 900) {
                    if ($original_width < $original_height) {
                        $new_width = 1200;
                        $new_height = ceil($original_height * 900 / $original_width);
                    } else {
                        $new_height = 900;
                        $new_width = ceil($original_width * 1200 / $original_height);
                    }

                    //save used image
                    $img->toJpeg(90)->save($destinationPath . $filename);
                    $img->scale(width: $new_width, height: $new_height)->toJpeg(90)->save($destinationPath . '' . $filename);

                    //create thumbnail
                    if ($original_width < $original_height) {
                        $thumbnails_width = 400;
                        $thumbnails_height = ceil($new_height * 300 / $new_width);
                    } else {
                        $thumbnails_height = 300;
                        $thumbnails_width = ceil($new_width * 400 / $new_height);
                    }
                    $img->scale(width: $thumbnails_width, height: $thumbnails_height)->toJpeg(90)->save($ThumbnailsDestinationPath . '' . $filename);
                } else {
                    //save used image
                    $img->toJpeg(90)->save($destinationPath . $filename);
                    //create thumbnail
                    if ($original_width < $original_height) {
                        $thumbnails_width = 400;
                        $thumbnails_height = ceil($original_height * 300 / $original_width);
                    } else {
                        $thumbnails_height = 300;
                        $thumbnails_width = ceil($original_width * 400 / $original_height);
                    }
                    $img->scale(width: $thumbnails_width, height: $thumbnails_height)->toJpeg(90)->save($ThumbnailsDestinationPath . '' . $filename);
                }
                $inputs[$input_file]=$uploaded;
            }

        }
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
}

