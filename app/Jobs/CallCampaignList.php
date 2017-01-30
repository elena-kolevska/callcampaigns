<?php

namespace App\Jobs;

use App\Campaigns\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * @property Campaign campaign
 */
class CallCampaignList implements ShouldQueue
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
        $done = false;

        $client = new \Twilio\Rest\Client(
            getenv('TWILIO_ACCOUNT_SID'),
            getenv('TWILIO_AUTH_TOKEN')
        );
        // We will be grabbing 500 numbers at a time from the db,
        // so we don't have to load the full campaign in memory
        while(!$done){
            $numbers = $this->campaign->phoneNumbers()
                ->where('call_status_id', config('aj.call_statuses')['not_called']['id'])
                ->limit(500)
                ->get();
            if (!$numbers->count()){
                $done = true;
                continue;
            }

            foreach ($numbers as $number) {
                try {
                    $client->calls->create(
                        $number->phone_number, // The visitor's phone number
                        '351308811914', // A Twilio number in your account
                        array(
                            "url" => url("/api/v1/campaigns/{$this->campaign->id}/client/{$number->id}/answer")
                        )
                    );
                    $number->call_status_id = config('aj.call_statuses')['call_trigerred']['id'];
                    $number->save();
                } catch (Exception $e) {
                    // Failed calls will throw
                    \Bugsnag::notifyException($e);
                    return $e;
                }

            }

        }

        $this->campaign->status = 'completed';
        $this->campaign->save();

    }
}
