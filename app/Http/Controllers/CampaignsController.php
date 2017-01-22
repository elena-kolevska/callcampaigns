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

        return response()->json($campaign);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
//dd($request->all());


        //Validate
        $this->validate($request, [
            'name' => 'required|max:255',
            'locale' => 'required',
            'message' => 'required',
        ]);
//dd("gu");


        //Save campaign
        $campaign = \App\Campaigns\Campaign::createNew(\Auth::user(), $request);

        return response()->json($campaign);
    }
}
