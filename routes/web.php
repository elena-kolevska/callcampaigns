<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index');

Route::get('test', function () {
//    $job = (new App\Jobs\ProcessCampaignList("path", 1))->onQueue('campaign_lists');
//dd($job);


    \Cache::put("testkey12","aaaty",1);
    return \Cache::get("testkey12");
    return "hey";
});