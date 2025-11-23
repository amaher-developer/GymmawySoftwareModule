<?php

namespace Modules\Software\Http\Controllers\Admin;

use Illuminate\Container\Container as Application;
use Modules\Generic\Http\Controllers\Admin\GenericAdminController;
use Modules\Software\Http\Requests\GymActivityRequest;
use Modules\Software\Repositories\GymActivityRepository;
use Modules\Software\Models\GymActivity;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class GymActivityAdminController extends GenericAdminController
{
     public $ActivityRepository;

         public function __construct()
         {
             parent::__construct();

             $this->ActivityRepository=new GymActivityRepository(new Application);
         }


    public function index()
    {

        $title = 'activities List';
        $this->request_array = ['id'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $activities = $this->ActivityRepository->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $activities = $this->ActivityRepository->orderBy('id', 'DESC');
        }


             //apply filters
                $activities->when($id, function ($query) use ($id) {
                        $query->where('id','=', $id);
                });
                 $search_query = request()->query();

                       if (request()->ajax() && request()->exists('export')) {
                             $activities = $activities->get();
                             $array = $this->prepareForExport($activities);
                             $fileName = 'activities-' . Carbon::now()->toDateTimeString();
                             $file = Excel::create($fileName, function ($excel) use ($array) {
                                 $excel->setTitle('title');
                                 $excel->sheet('sheet1', function ($sheet) use ($array) {
                                     $sheet->fromArray($array);
                                 });
                             });
                             $file = $file->string('xlsx');
                             return [
                                 'name' => $fileName,
                                 'file' => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($file)
                             ];
                         }
                         if ($this->limit) {
                             $activities = $activities->paginate($this->limit);
                             $total = $activities->total();
                         } else {
                             $activities = $activities->get();
                             $total = $activities->count();
                         }


        return view('software::Admin.activity_admin_list', compact('activities','title', 'total', 'search_query'));
    }

    private function prepareForExport($data)
    {
        return array_map(function ($row) {
            return [
                'ID' => $row['id']
            ];
        }, $data->toArray());
    }

    public function create()
    {
        $title = 'Create Activity';
        return view('software::Admin.activity_admin_form', ['activity' => new GymActivity(),'title'=>$title]);
    }

    public function store(GymActivityRequest $request)
    {
        $activity_inputs = $this->prepare_inputs($request->except(['_token']));
        $this->ActivityRepository->create($activity_inputs);
        sweet_alert()->success('Done', 'Activity Added successfully');
        return redirect(route('listActivity'));
    }

    public function edit($id)
    {
        $activity =$this->ActivityRepository->withTrashed()->find($id);
        $title = 'Edit Activity';
        return view('software::Admin.activity_admin_form', ['activity' => $activity,'title'=>$title]);
    }

    public function update(GymActivityRequest $request, $id)
    {
        $activity =$this->ActivityRepository->withTrashed()->find($id);
        $activity_inputs = $this->prepare_inputs($request->except(['_token']));
        $activity->update($activity_inputs);
        sweet_alert()->success('Done', 'Activity Updated successfully');
        return redirect(route('listActivity'));
    }

    public function destroy($id)
      {
          $activity =$this->ActivityRepository->withTrashed()->find($id);
          if($activity->trashed())
          {
              $activity->restore();
          }
          else
          {
              $activity->delete();
          }
        sweet_alert()->success('Done', 'Activity Deleted successfully');
        return redirect(route('listActivity'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

                $destinationPath = base_path($this->ActivityRepository->model()::$uploads_path);
                $ThumbnailsDestinationPath = base_path($this->ActivityRepository->model()::$thumbnails_uploads_path);
        
                if (!File::exists($destinationPath)) {
                    File::makeDirectory($destinationPath, $mode = 0777, true, true);
                }
                if (!File::exists($ThumbnailsDestinationPath)) {
                    File::makeDirectory($ThumbnailsDestinationPath, $mode = 0777, true, true);
                }
                if (request()->hasFile($input_file)) {
                    $file = request()->file($input_file);
        
                    if (is_image($file->getRealPath())) {
                        $filename = rand(0, 20000) . time() . '.' . $file->getClientOriginalExtension();
        
        
                        $uploaded = $filename;
        
                        $img = Image::make($file);
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
                            $img->encode('jpg', 90)->save($destinationPath . $filename);
                            $img->resize($new_width, $new_height, function ($constraint) {
                                $constraint->aspectRatio();
                            })->encode('jpg', 90)->save($destinationPath . '' . $filename);
        
                            //create thumbnail
                            if ($original_width < $original_height) {
                                $thumbnails_width = 400;
                                $thumbnails_height = ceil($new_height * 300 / $new_width);
                            } else {
                                $thumbnails_height = 300;
                                $thumbnails_width = ceil($new_width * 400 / $new_height);
                            }
                            $img->resize($thumbnails_width, $thumbnails_height, function ($constraint) {
                                $constraint->aspectRatio();
                            })->encode('jpg', 90)->save($ThumbnailsDestinationPath . '' . $filename);
                        } else {
                            //save used image
                            $img->encode('jpg', 90)->save($destinationPath . $filename);
                            //create thumbnail
                            if ($original_width < $original_height) {
                                $thumbnails_width = 400;
                                $thumbnails_height = ceil($original_height * 300 / $original_width);
                            } else {
                                $thumbnails_height = 300;
                                $thumbnails_width = ceil($original_width * 400 / $original_height);
                            }
                            $img->resize($thumbnails_width, $thumbnails_height, function ($constraint) {
                                $constraint->aspectRatio();
                            })->encode('jpg', 90)->save($ThumbnailsDestinationPath . '' . $filename);
                        }
                            $inputs[$input_file]=$uploaded;
                    }
        
                }
        

        !$inputs['deleted_at']?$inputs['deleted_at']=null:'';

        return $inputs;
    }

}

