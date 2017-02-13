<?php

use App\Campaigns\Campaign;
use App\Campaigns\CampaignOption;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Jobs\ProcessCampaignList;

class CreateCampaignTest extends TestCase
{
    use DatabaseMigrations;
    use WithoutMiddleware;


    /** @test */
    public function creates_campaign_with_call_options_on_endpoint_call()
    {
        $user = factory(User::class)->make();
        $this->be($user);

        $campaign = factory(Campaign::class)->make();

        for($i=0; $i < 5; $i++){
            $campaign_options[] = factory(CampaignOption::class)->make([
                'count' => 0,
            ])->toArray();
        }
        $campaign['options'] = json_encode($campaign_options);

        $this->json('POST', 'api/v1/campaigns/', $campaign->toArray());

        // We're not passing a file in here
        // So a ProcessCampaignList job shouldn't be queued
        $this->doesntExpectJobs(ProcessCampaignList::class);
        $this->assertTrue($this->response->isOk());
        $this->seeJson([
                    "name"=>$campaign->name,
                    "company_id"=>$user->company_id,
                    "description"=>$campaign->description,
                    "message"=>$campaign->message,
                    "status"=>'importing',
                    "locale"=>$campaign->locale,
                ]);
        $this->seeJson([
                    "digit"=>"invalid_answer",
                ]);
        $this->seeJson([
                    "digit"=>"no_response",
                ]);
        $this->seeJsonStructure([
            'options' => ['*'=>['digit','label', 'message','thank_you_message']]
        ]);
    }

    /** @test */
    public function creates_campaign_without_call_options_on_endpoint_call()
    {
        $user = factory(User::class)->make();
        $this->be($user);

        $campaign = factory(Campaign::class)->make();

        $this->json('POST', 'api/v1/campaigns/', $campaign->toArray());

        // We're not passing a file in here
        // So a ProcessCampaignList job shouldn't be queued
        $this->doesntExpectJobs(ProcessCampaignList::class);
        $this->assertTrue($this->response->isOk());
        $this->seeJson([
            "name"=>$campaign->name,
            "company_id"=>$user->company_id,
            "description"=>$campaign->description,
            "message"=>$campaign->message,
            "status"=>'importing',
            "locale"=>$campaign->locale,
        ]);
        $this->seeJsonStructure([
            'options' => ['*'=>['digit','label', 'message','thank_you_message']]
        ]);
    }

    /** @test */
    public function name_is_needed_to_create_a_campaign()
    {
        $user = factory(User::class)->make();
        $this->be($user);

        $campaign = factory(Campaign::class)->make(['name'=>'']);

        $this->json('POST', 'api/v1/campaigns/', $campaign->toArray());

        $this->assertResponseStatus(422);
    }

    /** @test */
    public function message_is_needed_to_create_a_campaign()
    {
        $user = factory(User::class)->make();
        $this->be($user);

        $campaign = factory(Campaign::class)->make(['message'=>'']);

        $this->json('POST', 'api/v1/campaigns/', $campaign->toArray());

        $this->assertResponseStatus(422);
    }

    /** @test */
    public function locale_is_needed_to_create_a_campaign()
    {
        $user = factory(User::class)->make();
        $this->be($user);

        $campaign = factory(Campaign::class)->make(['locale'=>'']);

        $this->json('POST', 'api/v1/campaigns/', $campaign->toArray());

        $this->assertResponseStatus(422);
    }
}
