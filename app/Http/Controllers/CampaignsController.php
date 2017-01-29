<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CampaignsController extends Controller
{
    //TODO NOT TESTED!!!!!
    //TODO create a transformer
    public function index()
    {
        $campaigns = \App\Campaigns\Campaign::get();
        foreach ($campaigns as $campaign) {
            $campaign->setHumanReadableStatus();
        }
        return $campaigns;
    }
    public function show($id)
    {
        $campaign = \App\Campaigns\Campaign::where('company_id', \Auth::user()->company_id)->find($id);
        $campaign->setHumanReadableStatus();

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
}
