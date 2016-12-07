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
    public function store()
    {
        $input = request()->all();
        $input['company_id'] = request()->user()->company_id;
        return \App\Campaigns\Campaign::create($input);
    }
}
