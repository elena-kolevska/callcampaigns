<?php

use App\Campaigns\Campaign;
use App\Campaigns\CampaignPhoneNumber;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewCampaignResultsTest extends TestCase
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
                'call_status_id' => config('aj.call_statuses_by_keyword')['call_completed']['id'],
                'digit' => '',
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

        foreach ($campaign_phone_numbers as $campaign_phone_number) {
            $expected_json_response[] = [
                'phone_number' => $campaign_phone_number['phone_number'],
                'campaign_id' => $campaign->id,
                'digit' => ($campaign_phone_number['digit'] !== '') ? $campaign_phone_number['digit'] : "Didn't respond",
            ];
        }

        $this->json('GET', 'api/v1/campaigns/'. $campaign->id . '/results');

        $this->assertTrue($this->response->isOk());

        $this->seeJsonSubset($expected_json_response);
    }
}
