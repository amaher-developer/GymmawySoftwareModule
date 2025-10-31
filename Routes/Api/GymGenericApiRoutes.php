<?php

    Route::post('splash', 'Api\GymGenericApiController@splash')->middleware('api');
    Route::post('home', 'Api\GymGenericApiController@home')->middleware('api');
    Route::post('setting', 'Api\GymGenericApiController@setting')->middleware('api');

    Route::post('banner/{id}', 'Api\GymGenericApiController@banner')->middleware('api');
    Route::post('banners', 'Api\GymGenericApiController@banners')->middleware('api');

    Route::post('gallery', 'Api\GymGenericApiController@gallery')->middleware('api');

    Route::post('contact', 'Api\GymGenericApiController@contact')->middleware('api');

    Route::post('member_login', 'Api\GymGenericApiController@login')->middleware('api');
    Route::post('member_info', 'Api\GymGenericApiController@memberInfo')->middleware('auth:api');
    Route::post('member_block', 'Api\GymGenericApiController@memberBlock')->middleware('auth:api');
    Route::post('attendances', 'Api\GymGenericApiController@attendances')->middleware('auth:api');

    Route::post('pt-training-classes', 'Api\GymPTApiController@trainingClasses')->middleware('auth:api');

    Route::post('member-subscription-freeze', 'Api\GymGenericApiController@memberSubscriptionFreeze')->middleware('auth:api');


    Route::any('my-favorites', 'Api\GymGenericApiController@myFavorites')->middleware('auth:api');
    Route::any('my-notifications', 'Api\GymGenericApiController@myNotifications')->middleware('auth:api');
    Route::any('log_errors', 'Api\GymGenericApiController@logErrors')->middleware('api');
    Route::any('update_push_token', 'Api\GymGenericApiController@updatePushToken')->middleware('api');


?>
