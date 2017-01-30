<?php

namespace App\Jobs;

use App\Campaigns\Campaign;
use App\Campaigns\CampaignPhoneNumbers;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use League\Csv\Reader;

/**
 * @property Campaign campaign
 */
class ProcessCampaignList implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $campaign;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $file_path = storage_path() . '/app/' . $this->campaign->list_path_local;
        $reader = Reader::createFromPath($file_path);

        $offset = 1;
        $keys = array('phone_number');
        $results = $reader->fetchAssoc($keys);

        $i = 1;
        $j = 0;
        foreach ($results as $row) {
            // This block just ignores the list headers
            if ($j == 0){
                $j++;
                continue;
            }

            //do something here
            $row['campaign_id'] = $this->campaign->id;
            $row['client_response'] = '';
            $row['call_status_id'] = config('aj.call_statuses')['not_called']['id'];
            $row['call_hangup_status'] = '';
            $data_to_be_inserted[] = $row;
            $i++;

            if ($i >= 50){
                CampaignPhoneNumbers::insert($data_to_be_inserted);
                $data_to_be_inserted = [];
            }
        }

        if (count($data_to_be_inserted)){
            CampaignPhoneNumbers::insert($data_to_be_inserted);
        }

        // Clear the memory, cause this worker will be running in a daemon
        $data_to_be_inserted = null;
        unset($data_to_be_inserted);

        $this->campaign->list_content_processed = true;
        $this->campaign->status = 'ready';
        $this->campaign->save();
        \Log::info('Job just ran ' . $this->campaign->id);
    }


    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Send user notification of failure, etc...
    }
}
