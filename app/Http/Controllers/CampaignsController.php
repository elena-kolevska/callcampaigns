<?php

namespace App\Http\Controllers;

use App\Campaigns\Campaign;
use App\Campaigns\CampaignPhoneNumber;
use Illuminate\Http\Request;

class CampaignsController extends Controller
{


    //TODO NOT TESTED!!!!!
    //TODO create a transformer
    /**
     * @var Campaign
     */
    private $campaignModel;

    /**
     * CampaignsController constructor.
     */
    public function __construct(Campaign $campaignModel)
    {
        $this->campaignModel = $campaignModel;
    }

    public function index()
    {
        $campaigns = $this->campaignModel->where('company_id', \Auth::user()->company_id)
            ->orderBy('completed_at', 'desc')
            ->get();
        foreach ($campaigns as $campaign) {
            $campaign->formatData();
        }
        return $campaigns;
    }
    public function show($id)
    {
        $campaign = $this->campaignModel->with('options')->find($id);
        $this->checkRights($campaign->company_id);

        $campaign->formatData();
        $campaign->updateResults();

        return response()->json($campaign);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        //Validate
        $this->validate($request, [
            'name' => 'required|max:255',
            'locale' => 'required',
            'message' => 'required',
        ]);

        //Save campaign
        $campaign = \App\Campaigns\Campaign::createNew(\Auth::user(), $request);

        return response()->json($campaign);
    }

    public function start($campaign_id)
    {
        $campaign = \App\Campaigns\Campaign::find($campaign_id);
        $this->checkRights($campaign->company_id);

        $campaign->start();
        $campaign->save();

        return $campaign;
    }

    public function callClientAnswer($campaign_id, $campaign_number_id,  CampaignPhoneNumber $campaignPhoneNumberModel)
    {
        $campaign = $this->campaignModel->find($campaign_id);

        // Find and update the campaign phone number. Set status to "Call in progress"
        $campaign_phone_number = $campaignPhoneNumberModel->find($campaign_number_id);
        $campaign_phone_number->call_status_id = config('aj.call_statuses_by_keyword')['call_in_progress']['id'];
        $campaign_phone_number->save();

        // Form the spoken message out of the main message plus the options
        $message = $campaign->message;
        $options = json_decode($campaign->options);
        foreach ($options as $option) {
            $message .= '. ' . $option->message;
        }

        // Settings for the speak element
        $speak_options = [
            'voice' => 'alice',
            'language' => $campaign->locale
        ];

        $twiml = new \Twilio\Twiml();

        // If no input was sent, use the <Gather> verb to collect user input
        $gather = $twiml->gather(array('numDigits' => 1, 'action'=>"/api/v1/campaigns/{$campaign_id}/client/{$campaign_number_id}/gather"));
        // use the <Say> verb to request input from the user
        $gather->say($message, $speak_options);

        // If the user doesn't enter input, loop
        $twiml->redirect("/api/v1/campaigns/{$campaign_id}/client/{$campaign_number_id}/answer");

        $response = \Response::make($twiml, 200);
        $response->header('Content-Type', 'text/xml');
        return $response;
    }

    public function callReceiveClientInput($campaign_id, $campaign_number_id,  CampaignPhoneNumber $campaignPhoneNumberModel, Request $request)
    {
        $campaign = $this->campaignModel->find($campaign_id);
        $campaign_phone_number = $campaignPhoneNumberModel->find($campaign_number_id);

        // Only save the incoming Digit if the call is currently in process
        // Just as some extra protection
        if ($campaign_phone_number->call_status_id != config('aj.call_statuses_by_keyword')['call_in_progress']['id']){
            return "Call not active";
        }


        $options = json_decode($campaign->options);
        $selected_digit = $request->input('Digits');
        $thank_you_message = '';

        foreach ($options as $option) {
            if ($option->digit == $selected_digit){
                $thank_you_message = $option->thank_you_message;
                break;
            }
        }

        // Settings for the speak element
        $speak_options = [
            'voice' => 'alice',
            'language' => $campaign->locale
        ];

        $twiml = new \Twilio\Twiml();

        if (!$request->has('Digits')){
            $twiml->redirect("/api/v1/campaigns/{$campaign_id}/client/{$campaign_number_id}/answer");
        }

        // Find and update the campaign phone number. Set status to "Call in progress"
        $campaign_phone_number->call_status_id = config('aj.call_statuses_by_keyword')['call_completed']['id'];
        $campaign_phone_number->digit = $selected_digit;
        $campaign_phone_number->save();

        $twiml->say($thank_you_message, $speak_options);
        $twiml->hangup();

        $response = \Response::make($twiml, 200);
        $response->header('Content-Type', 'text/xml');
        return $response;
    }

    public function callStatusChange()
    {
        return "Ok";
    }

    public function results($id)
    {
        $campaign = $this->campaignModel->find($id);
        $this->checkRights($campaign->company_id);


        $results = \DB::table('campaign_phone_numbers')
            ->where('campaign_id',$campaign->id)
            ->select('phone_number','digit','campaign_id','call_status_id')
            ->get();

        foreach ($results as $result) {
            $result->status = config('aj.call_statuses_by_id')[$result->call_status_id]['label'];
            if (!$result->digit){
                $result->digit = "Didn't respond";
            }
        }

        return $results;
    }
    public function downloadResults($campaign_id, $digit)
    {

    }
}
