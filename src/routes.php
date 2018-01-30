<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Landers\LaravelPlus\Components'], function (){
    //极验
    Route::group(['prefix' => 'geetest'], function () {
        Route::get('captcha', 'Geetest\GeetestController@captcha');
    });
});