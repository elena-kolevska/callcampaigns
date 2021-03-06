<?php

use App\Campaigns\Campaign;
use App\Campaigns\CampaignOption;
use App\Campaigns\CampaignPhoneNumber;
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
        for($i=0; $i < 5; $i++){
            $option = factory(CampaignOption::class)->make([
                'count' => $faker->randomNumber,
            ]);
            $campaign->options()->save($option);
        }

        //Add 5 phone numbers in campaign
        $campaign_phone_numbers = [
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses_by_keyword')['call_completed']['id'],
                'digit' => '1',
                'call_hangup_status' => '',
            ],
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses_by_keyword')['call_completed']['id'],
                'digit' => '1',
                'call_hangup_status' => '',
            ],
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses_by_keyword')['call_completed']['id'],
                'digit' => '1',
                'call_hangup_status' => '',
            ],
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses_by_keyword')['call_completed']['id'],
                'digit' => '2',
                'call_hangup_status' => '',
            ],
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses_by_keyword')['call_completed']['id'],
                'digit' => '2',
                'call_hangup_status' => '',
            ],
            [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses_by_keyword')['call_completed']['id'],
                'digit' => '3',
                'call_hangup_status' => '',
            ],

        ];
        CampaignPhoneNumber::insert($campaign_phone_numbers);


        $this->json('GET', 'api/v1/campaigns/'. $campaign->id);

        // We do this so we can easily get the options by digit
        $campaign->formatData();

        $expected_campaign_result = [
            ['count'=>3, 'digit'=>1, "label" => $campaign->options_by_digit[1]],
            ['count'=>2, 'digit'=>2, "label" => $campaign->options_by_digit[2]],
            ['count'=>1, 'digit'=>3, "label" => $campaign->options_by_digit[3]],
        ];

        $this->assertTrue($this->response->isOk());

        $this->seeJsonSubset([
                    "company_id"=>$campaign->company_id,
                    "name"=>$campaign->name,
                    "description"=>$campaign->description,
                    "message"=>$campaign->message,
                    "locale"=>$campaign->locale,
//                    "options"=>$campaign->options,
                    "human_readable_status"=>'Completed',
                ]);
        $this->seeJsonStructure([
            'options' => ['*'=>['digit','label', 'message','thank_you_message']]
        ]);

    }

}
