<?php

namespace Modules\Software\Http\Controllers\Admin;

use Illuminate\Container\Container as Application;
use Modules\Generic\Http\Controllers\Admin\GenericAdminController;
use Modules\Software\Http\Requests\GymUserRequest;
use Modules\Software\Repositories\GymUserRepository;
use Modules\Software\Models\GymUser;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class GymUserAdminController extends GenericAdminController
{
     public $GymUserRepository;

         public function __construct()
         {
             parent::__construct();

             $this->GymUserRepository=new GymUserRepository(new Application);
         }


    public function index()
    {

        $title = 'gymusers List';
        $this->request_array = ['id'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $gymusers = $this->GymUserRepository->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $gymusers = $this->GymUserRepository->orderBy('id', 'DESC');
        }


             //apply filters
                $gymusers->when($id, function ($query) use ($id) {
                        $query->where('id','=', $id);
                });
                 $search_query = request()->query();

                       if (request()->ajax() && request()->exists('export')) {
                             $gymusers = $gymusers->get();
                             $array = $this->prepareForExport($gymusers);
                             $fileName = 'gymusers-' . Carbon::now()->toDateTimeString();
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
                             $gymusers = $gymusers->paginate($this->limit);
                             $total = $gymusers->total();
                         } else {
                             $gymusers = $gymusers->get();
                             $total = $gymusers->count();
                         }


        return view('software::Admin.gymuser_admin_list', compact('gymusers','title', 'total', 'search_query'));
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
        $title = 'Create GymUser';
        return view('software::Admin.gymuser_admin_form', ['gymuser' => new GymUser(),'title'=>$title]);
    }

    public function store(GymUserRequest $request)
    {
        $gymuser_inputs = $this->prepare_inputs($request->except(['_token']));
        $this->GymUserRepository->create($gymuser_inputs);
        sweet_alert()->success('Done', 'GymUser Added successfully');
        return redirect(route('listGymUser'));
    }

    public function edit($id)
    {
        $gymuser =$this->GymUserRepository->withTrashed()->find($id);
        $title = 'Edit GymUser';
        return view('software::Admin.gymuser_admin_form', ['gymuser' => $gymuser,'title'=>$title]);
    }

    public function update(GymUserRequest $request, $id)
    {
        $gymuser =$this->GymUserRepository->withTrashed()->find($id);
        $gymuser_inputs = $this->prepare_inputs($request->except(['_token']));
        $gymuser->update($gymuser_inputs);
        sweet_alert()->success('Done', 'GymUser Updated successfully');
        return redirect(route('listGymUser'));
    }

    public function destroy($id)
      {
          $gymuser =$this->GymUserRepository->withTrashed()->find($id);
          if($gymuser->trashed())
          {
              $gymuser->restore();
          }
          else
          {
              $gymuser->delete();
          }
        sweet_alert()->success('Done', 'GymUser Deleted successfully');
        return redirect(route('listGymUser'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

                $destinationPath = base_path($this->GymUserRepository->model()::$uploads_path);
                $ThumbnailsDestinationPath = base_path($this->GymUserRepository->model()::$thumbnails_uploads_path);
        
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
