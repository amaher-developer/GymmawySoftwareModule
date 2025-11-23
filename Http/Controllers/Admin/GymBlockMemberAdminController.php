<?php

namespace Modules\Software\Http\Controllers\Admin;

use Illuminate\Container\Container as Application;
use Modules\Generic\Http\Controllers\Admin\GenericAdminController;
use Modules\Software\Http\Requests\GymBlockMemberRequest;
use Modules\Software\Repositories\GymBlockMemberRepository;
use Modules\Software\Models\GymBlockMember;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class GymBlockMemberAdminController extends GenericAdminController
{
     public $GymBlockMemberRepository;

         public function __construct()
         {
             parent::__construct();

             $this->GymBlockMemberRepository=new GymBlockMemberRepository(new Application);
         }


    public function index()
    {

        $title = 'gymblockmembers List';
        $this->request_array = ['id'];
        $request_array = $this->request_array;
        foreach ($request_array as $item) $$item = request()->has($item) ? request()->$item : false;
        if(request('trashed'))
        {
            $gymblockmembers = $this->GymBlockMemberRepository->onlyTrashed()->orderBy('id', 'DESC');
        }
        else
        {
            $gymblockmembers = $this->GymBlockMemberRepository->orderBy('id', 'DESC');
        }


             //apply filters
                $gymblockmembers->when($id, function ($query) use ($id) {
                        $query->where('id','=', $id);
                });
                 $search_query = request()->query();

                       if (request()->ajax() && request()->exists('export')) {
                             $gymblockmembers = $gymblockmembers->get();
                             $array = $this->prepareForExport($gymblockmembers);
                             $fileName = 'gymblockmembers-' . Carbon::now()->toDateTimeString();
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
                             $gymblockmembers = $gymblockmembers->paginate($this->limit);
                             $total = $gymblockmembers->total();
                         } else {
                             $gymblockmembers = $gymblockmembers->get();
                             $total = $gymblockmembers->count();
                         }


        return view('software::Admin.gymblockmember_admin_list', compact('gymblockmembers','title', 'total', 'search_query'));
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
        $title = 'Create GymBlockMember';
        return view('software::Admin.gymblockmember_admin_form', ['gymblockmember' => new GymBlockMember(),'title'=>$title]);
    }

    public function store(GymBlockMemberRequest $request)
    {
        $gymblockmember_inputs = $this->prepare_inputs($request->except(['_token']));
        $this->GymBlockMemberRepository->create($gymblockmember_inputs);
        sweet_alert()->success('Done', 'GymBlockMember Added successfully');
        return redirect(route('listGymBlockMember'));
    }

    public function edit($id)
    {
        $gymblockmember =$this->GymBlockMemberRepository->withTrashed()->find($id);
        $title = 'Edit GymBlockMember';
        return view('software::Admin.gymblockmember_admin_form', ['gymblockmember' => $gymblockmember,'title'=>$title]);
    }

    public function update(GymBlockMemberRequest $request, $id)
    {
        $gymblockmember =$this->GymBlockMemberRepository->withTrashed()->find($id);
        $gymblockmember_inputs = $this->prepare_inputs($request->except(['_token']));
        $gymblockmember->update($gymblockmember_inputs);
        sweet_alert()->success('Done', 'GymBlockMember Updated successfully');
        return redirect(route('listGymBlockMember'));
    }

    public function destroy($id)
      {
          $gymblockmember =$this->GymBlockMemberRepository->withTrashed()->find($id);
          if($gymblockmember->trashed())
          {
              $gymblockmember->restore();
          }
          else
          {
              $gymblockmember->delete();
          }
        sweet_alert()->success('Done', 'GymBlockMember Deleted successfully');
        return redirect(route('listGymBlockMember'));
    }

    private function prepare_inputs($inputs)
    {
        $input_file = 'image';
        $uploaded='';

                $destinationPath = base_path($this->GymBlockMemberRepository->model()::$uploads_path);
                $ThumbnailsDestinationPath = base_path($this->GymBlockMemberRepository->model()::$thumbnails_uploads_path);
        
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

