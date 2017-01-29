<?php

namespace App\Campaigns;

use App\Jobs\CallCampaignList;
use App\Jobs\ProcessCampaignList;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    const STATUSES = [
            'importing' => 'Importing...',
            'ready' => 'Ready to be called',
            'calling' => 'Calling...',
            'completed' => 'Completed'
        ];

    protected $guarded = [];
    protected $fillable = ['company_id', 'name','description', 'locale', 'message', 'options', 'list_path_local','list_path_remote', 'status'];
    protected $casts = [
        'id' => 'integer',
        'company_id' => 'integer',
        'list_content_processed' => 'boolean'
    ];

    static function createNew($user, $request)
    {
        if (!$user){
            return "You need to specify a user";
        }

        // First let's save the file, if one was uploaded
        $file = $request->file('list');
        $file_path = '';
        if ($file){
            $file_path = $request->file('list')->store('lists_'.$user->company_id);
        }

        $data = $request->only(['name','description','locale','message','options']);
        $data['list_path_local'] = $file_path;
        $data['user_id'] = $user->id;
        $data['company_id'] = $user->company_id;
        $data['status'] = 'importing';

        $campaign =  parent::create($data);

        // Queue the job of processing the list
        if ($file){
            $job = (new ProcessCampaignList($campaign))->onQueue('campaign_lists_to_be_processed');
            dispatch($job);
        }

        return $campaign;
    }

    public function phoneNumbers()
    {
        return $this->hasMany('App\Campaigns\CampaignPhoneNumbers');
    }

    public function setHumanReadableStatus()
    {
        $this->human_readable_status = self::STATUSES[$this->status];
    }

    public function start()
    {
        $job = (new CallCampaignList($this))->onQueue('campaign_lists_to_be_called');
        dispatch($job);

        $this->status = 'calling';
    }

}
