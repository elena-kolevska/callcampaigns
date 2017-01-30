<?php

use App\Campaigns\Campaign;
use App\Campaigns\CampaignPhoneNumbers;
use App\Jobs\CallCampaignList;
use App\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CallCampaignListTest extends TestCase
{
    use DatabaseMigrations;
    use WithoutMiddleware;

    /** @test */
    public function adds_campaign_to_queue_when_campaign_is_started()
    {
        $user = factory(User::class)->make();
        $this->be($user);

        $campaign = factory(Campaign::class)->create([
            'company_id' => $user->company_id
        ]);

        $this->expectsJobs(CallCampaignList::class);
        $this->json('POST', 'api/v1/campaigns/'. $campaign->id . '/start');
        $this->assertTrue($this->response->isOk());
    }

    /** @test */
    public function exception_is_thrown_if_a_non_superadmin_user_tries_to_start_other_companys_campaign()
    {
        $this->disableExceptionHandling();

        $user = factory(User::class)->make();
        $this->be($user);

        $campaign = factory(Campaign::class)->create([
            'company_id' => $user->company_id + 1
        ]);

        $this->expectException( AccessDeniedHttpException::class);
        $this->json('POST', 'api/v1/campaigns/'. $campaign->id . '/start');
    }

    /** @test */
    public function exception_is_not_thrown_when_a_superadmin_tries_to_start_other_companys_campaign()
    {
        $user = factory(User::class)->make([
            'is_superadmin' => 1
        ]);
        $this->be($user);

        $this->disableExceptionHandling();
        $campaign = factory(Campaign::class)->create([
            'company_id' => $user->company_id + 1
        ]);

        $this->json('POST', 'api/v1/campaigns/'. $campaign->id . '/start');
        $this->assertTrue($this->response->isOk());
    }

    /** @test */
    public function triggers_calls_to_numbers_from_campaign_list()
    {
        $faker = Faker\Factory::create();

        //Arrange
        // Create a campaign
        $campaign = factory(Campaign::class)->create();

        //Add 5 phone numbers in campaign
        for ($i=0; $i < 5; $i++) {
            $rows[] = [
                'phone_number' => $faker->e164PhoneNumber,
                'campaign_id' => $campaign->id,
                'call_status_id' => config('aj.call_statuses')['not_called']['id'],
                'client_response' => '',
                'call_hangup_status' => '',
            ];
        }
        CampaignPhoneNumbers::insert($rows);

        //Act
        $jobProcessor = new CallCampaignList($campaign);
        $jobProcessor->handle();

        //Assert
        //5 calls have been triggered
        // Campaign status is "completed"

    }

}
