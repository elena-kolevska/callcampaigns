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

        $campaign->formatData();
        return $campaign;
    }

    public function phoneNumbers()
    {
        return $this->hasMany('App\Campaigns\CampaignPhoneNumbers');
    }

    public function start()
    {
        $job = (new CallCampaignList($this))->onQueue('campaign_lists_to_be_called');
        dispatch($job);

        $this->status = 'calling';
    }





    // Data viewing
    public function formatData()
    {
        $this->setHumanReadableStatus();
        $this->setOptions();
        $this->setOptionsById();
        $this->setResults();
    }

    public function setOptions()
    {
        $this->options = json_decode($this->options);
    }

    /**
     *  We use this to show the option label instead of just showing the digit
     */
    public function setOptionsById()
    {
        $options_by_digit[0] = "Didn't respond";
        foreach ($this->options as $option) {
            $options_by_digit[$option->digit] = $option->label ?? $option->message;
        }
        $this->options_by_digit = $options_by_digit;
    }

    public function setHumanReadableStatus()
    {
        $this->human_readable_status = self::STATUSES[$this->status];
    }

    public function setResults()
    {
        $this->result = [];
        $report = [];
        $report_labels = [];
        $report_data = [];

        $results = $this->phoneNumbers()
            ->where('call_status_id', config('aj.call_statuses')['call_completed']['id'])
            ->select('client_response', \DB::raw('count(*) as count'))
            ->groupBy('client_response')
            ->orderBy('client_response')
            ->get();

        foreach ($results as $result) {
            $report[] = [
                'digit' => (int) $result->client_response,
                'label' => $this->options_by_digit[$result->client_response] ?? "Didn't respond",
                'count' => (int) $result->count,
            ];
            $report_labels[] = $this->options_by_digit[$result->client_response] ?? "Pressed {$result->client_response}";
            $report_data[] = (int) $result->count;
        }

        $this->result = $report;
        $this->report_labels = $report_labels;
        $this->report_data = $report_data;
    }

}
