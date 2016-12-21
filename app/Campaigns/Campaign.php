<?php

namespace App\Campaigns;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $guarded = [];
    protected $fillable = ['company_id', 'name','description', 'locale', 'message'];

}
