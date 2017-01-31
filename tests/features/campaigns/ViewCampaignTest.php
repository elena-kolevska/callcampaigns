<?php

use App\Campaigns\Campaign;
use App\Campaigns\CampaignPhoneNumbers;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewCampaignTest extends TestCase
{
    use DatabaseMigrations;
    use WithoutMiddleware;


    /** @test */
    public function views_campaign_with_results_on_endpoint_call()
    {
        $faker = Faker\Factory::create();

        $user = factory(User::class)->make();
        $this->be($user);

        $campaign = factory(Campaign::class)->create([
            'company_id' => $user->company_id,
            'status' => 'completed'
        ]);
        //Add 5 phone numbers in campaign
        $campaign_phone_numbers = [
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses')['call_completed']['id'],
                'client_response' => '1',
                'call_hangup_status' => '',
            ],
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses')['call_completed']['id'],
                'client_response' => '1',
                'call_hangup_status' => '',
            ],
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses')['call_completed']['id'],
                'client_response' => '1',
                'call_hangup_status' => '',
            ],
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses')['call_completed']['id'],
                'client_response' => '2',
                'call_hangup_status' => '',
            ],
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses')['call_completed']['id'],
                'client_response' => '2',
                'call_hangup_status' => '',
            ],
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses')['call_completed']['id'],
                'client_response' => '3',
                'call_hangup_status' => '',
            ],

        ];
        CampaignPhoneNumbers::insert($campaign_phone_numbers);

        $expected_campaign_result = [
            ['count'=>3, 'digit'=>1],
            ['count'=>2, 'digit'=>2],
            ['count'=>1, 'digit'=>3],
        ];

        $this->json('GET', 'api/v1/campaigns/'. $campaign->id);

        $this->assertTrue($this->response->isOk());

        $this->seeJson([
                    "company_id"=>$campaign->company_id,
                    "name"=>$campaign->name,
                    "description"=>$campaign->description,
                    "message"=>$campaign->message,
                    "locale"=>$campaign->locale,
                    "options"=>json_decode($campaign->options),
                    "human_readable_status"=>'Completed',
                    "result" => $expected_campaign_result
                ]);
    }
}
