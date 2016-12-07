<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


Route::post('login', 'LoginController@login');

Route::group(['prefix'=>'campaigns', 'middleware'=>['auth:api']], function() {
    Route::get('', 'CampaignsController@index');
    Route::post('', 'CampaignsController@store');
    Route::get('all', function(){
        return "all campaigns here";
    });

});

Route::group(['prefix'=>'customers'], function() {
    Route::get('{id}', 'CustomersController@show');
});