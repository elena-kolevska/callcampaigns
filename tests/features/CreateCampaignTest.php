<?php

use App\Campaigns\Campaign;
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
        $customer = factory(Campaign::class)->make();

        $this->json('POST', 'api/v1/campaigns/', $customer->toArray());

        $this->assertTrue($this->response->isOk());
        $this->seeJson([
                    "name"=>$customer->name,
                    "description"=>$customer->description,
                    "message"=>$customer->message,
                    "locale"=>$customer->locale,
                ]);
    }
}
