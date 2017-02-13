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
    protected $fillable = ['company_id', 'name','description', 'locale', 'message', 'list_path_local','list_path_remote', 'status', 'started_at', 'completed_at'];
    protected $casts = [
        'id' => 'integer',
        'company_id' => 'integer',
        'list_content_processed' => 'boolean'
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function options()
    {
        return $this->hasMany('App\Campaigns\CampaignOption');
    }

    public function phoneNumbers()
    {
        return $this->hasMany('App\Campaigns\CampaignPhoneNumber');
    }





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
        self::addCampaignOptions($data['options'], $campaign);

        // Queue the job of processing the list
        if ($file){
            $job = (new ProcessCampaignList($campaign))->onQueue('campaign_lists_to_be_processed');
            dispatch($job);
        }

        $campaign->formatData();
        return $campaign;
    }

    /**
     * @param $options
     * @param $campaign
     */
    private static function addCampaignOptions($options, $campaign)
    {
        // Handle no options
        // We are receiving json_encoded options, cause that's how the frontend sends them
        // i think it has to do with the form being multipart, but maybe i just suck at javascript
        $options = $options??json_encode([]);
        $options = json_decode($options, 1);

        // Prepare CallOption objects:
        $data = [];
        foreach ($options as $option) {
            $data[] = new \App\Campaigns\CampaignOption($option);
        }

        // Add option for people who didn't answer and for invalid answers
        $data[] = new \App\Campaigns\CampaignOption(['digit' => 'no_response', 'label' => "Didn't answer"]);
        $data[] = new \App\Campaigns\CampaignOption(['digit' => 'invalid_answer', 'label' => "Invalid answer"]);

        $campaign->options()->saveMany($data);
    }



    public function start()
    {
        $job = (new CallCampaignList($this))->onQueue('campaign_lists_to_be_called');
        dispatch($job);

        $this->status = 'calling';
        $this->started_at = \Carbon\Carbon::now();
    }


    // Data viewing
    public function formatData()
    {
        $this->setLabels();
        $this->setHumanReadableStatus();
//        $this->setOptionsById();

    }

    //TODO Unit test
    public function setHumanReadableStatus()
    {
        $this->human_readable_status = self::STATUSES[$this->status];
    }


    /**
     * Used for easy generation of chart.js charts
     *
     * @return $this
     */
    public function setLabels()
    {
        $report_labels = [];
        $report_data = [];

        foreach ($this->options as $option){
            $report_labels[] = $option->label;
            $report_data[] = (int) $option->count;
        }

        $this->report_labels = $report_labels;
        $this->report_data = $report_data;

        return $this;
    }

    public function updateResults()
    {
        $results = $this->phoneNumbers()
            ->where('call_status_id',3)  // We only check calls that have been answered by the customers
            ->groupBy('call_status_id')
            ->groupBy('digit')
            ->select(\DB::raw('count(*) as count'), 'digit')
            ->get();

        $valid_options = $this->options->pluck('digit');
        $invalid_answer_count = 0;
        foreach ($results as $result) {
            if (in_array($result->digit, $valid_options->toArray())){
                $this->options()->where('digit', $result->digit)->update(['count' => $result->count]);
            }elseif($result->digit == ''){
                $this->options()->where('digit', 'no_response')->update(['count' => $result->count]);
            }else{
                $invalid_answer_count++;
            }
        }

        // Since we can have many different invalid answers we just counted them in the for loop
        // and now we'll insert them
        if ($invalid_answer_count){
            $this->options()->where('digit', 'invalid_answer')->update(['count' => $invalid_answer_count]);
        }

        return $this;
    }
}
