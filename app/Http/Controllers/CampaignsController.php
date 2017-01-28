<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CampaignsController extends Controller
{
    public function index()
    {
        $campaigns = \App\Campaigns\Campaign::get();
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
}
