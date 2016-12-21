<?php

use App\Campaigns\Campaign;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CreateCampaignTest extends TestCase
{
    use DatabaseMigrations;
    use WithoutMiddleware;


    /** @test */
    public function creates_campaign_on_endpoint_call()
    {
        $user = factory(User::class)->make();
        $this->be($user);

        $campaign = factory(Campaign::class)->make();

        $this->json('POST', 'api/v1/campaigns/', $campaign->toArray());

        $this->assertTrue($this->response->isOk());
        $this->seeJson([
                    "name"=>$campaign->name,
                    "company_id"=>$user->company_id,
                    "description"=>$campaign->description,
                    "message"=>$campaign->message,
                    "locale"=>$campaign->locale,
                ]);
    }
}
