<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::get('my/get', 'MyController@get');
});

Route::group(['prefix' => 'v1'], function () {
    Route::get('my/get', 'MyController@get');
});

Route::post('email/send', 'MyEmail@send');

