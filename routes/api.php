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
Auth::routes();

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


//Route::post('login', 'App\Http\Controllers\Auth@login');

Route::group(['prefix'=>'campaigns', ], function() {
    Route::any('{id}/client/{campaign_phone_number_id}/answer', 'CampaignsController@callClientAnswer');
    Route::any('{id}/client/{campaign_phone_number_id}/gather', 'CampaignsController@callReceiveClientInput');
    Route::any('call_status', 'CampaignsController@callStatusChange');
});


Route::group(['prefix'=>'campaigns', 'middleware'=>['auth:api']], function() {
    Route::get('', 'CampaignsController@index');
    Route::get('{id}', 'CampaignsController@show');
    Route::post('{id}/start', 'CampaignsController@start');
    Route::post('{id}/answer', 'CampaignsController@callAnswer');
    Route::post('{id}/results', 'CampaignsController@downloadResults');
    Route::post('', 'CampaignsController@store');
    Route::get('all', function(){
        return "all campaigns here";
    });
});



Route::group(['prefix'=>'customers'], function() {
    Route::get('{id}', 'CustomersController@show');
});

Route::group(['prefix'=>'calls'], function() {
    Route::post('end', 'CallsController@end');
});


Route::get('start_call', function () {
    $client = new Twilio\Rest\Client(
        getenv('TWILIO_ACCOUNT_SID'),
        getenv('TWILIO_AUTH_TOKEN')
    );

    try {
        $client->calls->create(
            '351914232900', // The visitor's phone number
            '351308811914', // A Twilio number in your account
            array(
                "url" => "http://7bbba7e0.ngrok.io/api/v1/callback"
            )
        );
    } catch (Exception $e) {
        // Failed calls will throw
        return $e;
    }

    return "Starting call";
});

Route::any('callback', function () {
    // A message for Twilio's TTS engine to repeat
    $sayMessage = 'Obrigada pela sua chamada. Prima 1 para falar comigo. Prima 2 se não quer ser contactado.';

    $twiml = new \Twilio\Twiml();

    // If the user entered digits, process their request
    if (array_key_exists('Digits', $_POST)) {
        switch ($_POST['Digits']) {
            case 1:
                $twiml->say('Escolheu 1');
                break;
            case 2:
                $twiml->say('Escolheu 2');
                break;
            default:
                $twiml->say('Sorry, I don\'t understand that choice.');
        }
    } else {
        // If no input was sent, use the <Gather> verb to collect user input
        $gather = $twiml->gather(array('numDigits' => 1));
        // use the <Say> verb to request input from the user
        $gather->say($sayMessage, array('voice' => 'alice','language'=>'pt-PT'));

        // If the user doesn't enter input, loop
        $twiml->redirect('/api/v1/callback');
    }


    $response = Response::make($twiml, 200);
    $response->header('Content-Type', 'text/xml');
    return $response;
});