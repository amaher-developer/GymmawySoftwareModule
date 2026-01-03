<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your module. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::group(['prefix' => 'software'], function () {
    Route::get('/', function () {
        dd('This is the Software module index page. Build something great!');
    });
});

foreach (File::allFiles(__DIR__ . '/Admin') as $route) {
    require_once $route->getPathname();
}

// policy
Route::get('/web/policy', function (){
    if(@env('APP_WEBSITE')){
        try {
            echo @file_get_contents(@env('APP_WEBSITE').'policy.php') ?? '';
        }catch (Exception $e) {
            return $e->getMessage();
        }
    }

});
// policy
Route::get('/web/terms', function (){
    if(@env('APP_WEBSITE')) {
        try {
            echo @file_get_contents(@env('APP_WEBSITE') . 'terms.php') ?? '';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
});

Route::group(array('middleware' => ['front', 'lang', 'initialize_user'],'prefix' => (request()->segment(1) == 'ar' || request()->segment(1) == 'en') ? request()->segment(1) : ''), function () {

    Route::get('/web/templates', 'Front\GymWebsiteFrontController@templates')->name('web.templates');
    Route::get('/web/template', 'Front\GymWebsiteFrontController@template')->name('web.template');

//    Route::name('sw.updateSubscriptionsStatus')
//        ->get('update-subscriptions-status', 'Front\GymMemberFrontController@updateSubscriptionsStatus');

    Route::name('sw.login')
        ->get('login', 'Front\GymLoginFrontController@showLoginForm');
    Route::name('sw.login')
        ->post('login', 'Front\GymLoginFrontController@login');

    Route::name('sw.logout')
        ->middleware(['auth:sw'])
        ->get('logout', 'Front\GymLoginFrontController@logout');

    Route::name('sw.uploadExcel')
        ->middleware(['auth:sw'])
        ->get('upload-excel', 'Front\GymMemberFrontController@uploadExcel');

    Route::name('sw.uploadExcelStore')
        ->middleware(['auth:sw'])
        ->post('upload-excel-store', 'Front\GymMemberFrontController@uploadExcelStore');


    // $contains = str_contains(Request::url(), '/v1');
    // if($contains){
    //     Route::group(array('prefix' => '/v1'), function () {
    //         foreach (File::allFiles(__DIR__ . '/Front') as $route) {
    //             require_once $route->getPathname();
    //         }
    //     });
    // }else {
        foreach (File::allFiles(__DIR__ . '/Front') as $route) {
            require_once $route->getPathname();
        }
    // }
});


Route::get('/env-check', function () {
    return [
        'APP_NAME' => env('APP_NAME'),
        'DB_DATABASE' => env('DB_DATABASE'),
        'current_host' => $_SERVER['HTTP_HOST'] ?? 'N/A',
        'APP_NAME_AR' => env('APP_NAME_AR') ?? 'N/A'
    ];
});