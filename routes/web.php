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

Route::any('test', function () {
    $campaign = \App\Campaigns\Campaign::find(70);
    $options = json_decode($campaign->options);

    foreach ($options as $option) {
        if ($option->digit == 2){
            $thank_you_message = $option->thank_you_message;
            break;
        }
    }
dd($thank_you_message);




    \Log::info('Job just ran 5');
dd("ok");


    $campaign->status = 'completed';
    $campaign->completed_at = \Carbon\Carbon::now();
    $campaign->save();

});

