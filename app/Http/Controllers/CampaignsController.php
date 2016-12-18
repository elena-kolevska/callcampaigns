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
        return $campaign;
    }
    public function store()
    {
        $input = request()->all();
        $input['company_id'] = request()->user()->company_id;
        return \App\Campaigns\Campaign::create($input);
    }
}
