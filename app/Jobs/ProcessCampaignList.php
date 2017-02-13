<?php

namespace App\Jobs;

use App\Campaigns\Campaign;
use App\Campaigns\CampaignPhoneNumber;
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
        \Log::info("1");

        $file_path = storage_path() . '/app/' . $this->campaign->list_path_local;
        $reader = Reader::createFromPath($file_path);

        $keys = array('phone_number');
        $results = $reader->fetchAssoc($keys);
        \Log::info("2");

        $record_count = 0;
        $i = 1;
        $j = 0;
        \Log::info("3");

        foreach ($results as $row) {
            // This block just ignores the list headers
            if ($j == 0){
                $j++;
                continue;
            }

            //do something here
            $row['campaign_id'] = $this->campaign->id;
            $row['digit'] = '';
            $row['call_status_id'] = config('aj.call_statuses_by_keyword')['not_called']['id'];
            $row['call_hangup_status'] = '';
            $data_to_be_inserted[] = $row;
            $i++;
            $record_count++;

            if ($i >= 50){
                CampaignPhoneNumber::insert($data_to_be_inserted);
                $data_to_be_inserted = [];
                $i = 0;
            }
        }
        \Log::info("4");
        \Log::debug(count($data_to_be_inserted));

        if (count($data_to_be_inserted)){
            CampaignPhoneNumber::insert($data_to_be_inserted);
        }
        \Log::info("5");

        // Clear the memory, cause this worker will be running in a daemon
        $data_to_be_inserted = null;
        unset($data_to_be_inserted);

        $this->campaign->list_content_processed = true;
        $this->campaign->status = 'ready';
        \Log::info("6");

        $this->campaign->phone_number_count = $record_count;
        \Log::info("7");

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
