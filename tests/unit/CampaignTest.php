<?php

use App\Campaigns\Campaign;
use App\Campaigns\CampaignOption;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CampaignTest extends TestCase
{
    use DatabaseMigrations;
    use WithoutMiddleware;

    /** @test */
    public function sets_campaign_labels()
    {
        $campaign = factory(Campaign::class)->create();

        $campaign->options()->saveMany([
            factory(CampaignOption::class)->make(['label'=>'Label1', 'campaign_id'=>$campaign->id, 'count'=>1]),
            factory(CampaignOption::class)->make(['label'=>'Label2', 'campaign_id'=>$campaign->id, 'count'=>2]),
            factory(CampaignOption::class)->make(['label'=>'Label3', 'campaign_id'=>$campaign->id, 'count'=>3]),
        ]);

        $campaign->setLabels();

        $this->assertArraySubset(['Label1','Label2','Label3'], $campaign->report_labels);

    }

    /** @test */
    public function sets_human_readable_status()
    {
        $campaign = factory(Campaign::class)->make([
            'status' => 'calling'
        ]);

        $campaign->setHumanReadableStatus();

        $this->assertEquals('Calling...', $campaign->human_readable_status);

    }
    /** @test */
    public function updates_results()
    {
        $campaign = factory(Campaign::class)->create();

        $campaign->options()->saveMany([
            factory(CampaignOption::class)->make(['digit'=>'no_response', 'label'=>'No answer', 'campaign_id'=>$campaign->id, 'count'=>0]),
            factory(CampaignOption::class)->make(['digit'=>'invalid_answer', 'label'=>'Invalid answer', 'campaign_id'=>$campaign->id, 'count'=>0]),
            factory(CampaignOption::class)->make(['digit'=>'1', 'label'=>'Label1', 'campaign_id'=>$campaign->id, 'count'=>0]),
            factory(CampaignOption::class)->make(['digit'=>'2', 'label'=>'Label2', 'campaign_id'=>$campaign->id, 'count'=>0]),
            factory(CampaignOption::class)->make(['digit'=>'3', 'label'=>'Label3', 'campaign_id'=>$campaign->id, 'count'=>0]),
        ]);

        $campaign->phoneNumbers()->createMany([
            ['phone_number'=>'123', 'call_status_id'=>0, 'digit'=>''],
            ['phone_number'=>'123', 'call_status_id'=>3, 'digit'=>''],
            ['phone_number'=>'123', 'call_status_id'=>3, 'digit'=>''],
            ['phone_number'=>'123', 'call_status_id'=>3, 'digit'=>'1'],
            ['phone_number'=>'123', 'call_status_id'=>3, 'digit'=>'2'],
            ['phone_number'=>'123', 'call_status_id'=>3, 'digit'=>'2'],
            ['phone_number'=>'123', 'call_status_id'=>3, 'digit'=>'3'],
            ['phone_number'=>'123', 'call_status_id'=>3, 'digit'=>'4'],  // Invalid answers
            ['phone_number'=>'123', 'call_status_id'=>3, 'digit'=>'654'],  // Invalid answer
        ]);

        $campaign->updateResults();

        $this->assertEquals(2, $campaign->options()->where('digit','no_response')->first()->count);
        $this->assertEquals(1, $campaign->options()->where('digit','1')->first()->count ?? 0);
        $this->assertEquals(2, $campaign->options()->where('digit','2')->first()->count ?? 0);
        $this->assertEquals(1, $campaign->options()->where('digit','3')->first()->count ?? 0);
        $this->assertEquals(2, $campaign->options()->where('digit','invalid_answer')->first()->count ?? 0);
    }


}
