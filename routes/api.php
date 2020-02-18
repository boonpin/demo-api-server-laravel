<?php

Route::group(['prefix' => 'general'], function () {
    Route::get('/info', 'GeneralController@getInfo');
});

Route::group(['prefix' => 'auth'], function () {
    Route::get('/user', 'AuthController@getLoggedUser');
    Route::post('/login', 'AuthController@doLogin');
    Route::post('/refresh', 'AuthController@refreshToken');
    Route::get('/validate', 'AuthController@checkLogin');
    Route::get('/logout', 'AuthController@logout');
});
