<?php

use App\Campaigns\Campaign;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewCampaignTest extends TestCase
{
    use DatabaseMigrations;
    use WithoutMiddleware;


    /** @test */
    public function creates_campaign_on_endpoint_call()
    {
        $user = factory(User::class)->make();
        $this->be($user);

        $campaign = factory(Campaign::class)->create([
            'company_id' => $user->company_id
        ]);

        $this->json('GET', 'api/v1/campaigns/'. $campaign->id);

        $this->assertTrue($this->response->isOk());

        $this->seeJson([
                    "company_id"=>$campaign->company_id,
                    "name"=>$campaign->name,
                    "description"=>$campaign->description,
                    "message"=>$campaign->message,
                    "locale"=>$campaign->locale,
                    "human_readable_status"=>'Importing...',
                ]);
    }
}
