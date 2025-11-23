<div class="card ">
    <div class="card-body text-center">
        <img src="{{@$member->image}}" alt="avatar" class="rounded-circle img-fluid"
             style="width: 150px;height: 150px;object-fit: cover">
        <h5 class="my-3">{{@$member->name}}</h5>
        <p class="text-muted mb-1">{{@$member->code}}</p>
        <p class="text-muted mb-4"><img  src="{{asset('uploads/barcodes/'.@$member->code.'.png')}}"></p>
        <div class="d-flex justify-content-center mb-2">
            <a type="button"  href="{{route('sw.customerLogout')}}">{{trans('admin.logout')}}</a>
        </div>
    </div>
</div>
@if(@env('APP_MAX_CAPACITY_NUM') && isset($number_of_attendees))
<br/><br/>
<div class=" front-skills">
    <h3 class="block">{{trans('sw.capacity_gym')}}</h3>
    <span> {{(($number_of_attendees/(int)env('APP_MAX_CAPACITY_NUM')) * 100)}}%</span>
    <div class="progress">
        <div role="progressbar" class="progress-bar" style="width: {{(($number_of_attendees/(int)env('APP_MAX_CAPACITY_NUM')) * 100)}}%;"></div>
    </div>

</div>
@endif


