<?php

use App\Campaigns\Campaign;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use League\Csv\Reader;
use League\Csv\Writer;

use App\Jobs\ProcessCampaignList;

class ProcessCampaignListTest extends TestCase
{
    use DatabaseMigrations;
    use WithoutMiddleware;


    /** @test */
    public function imports_phone_numbers_in_campaign_list()
    {

        //Arrange
        $faker = Faker\Factory::create();

        $campaign = factory(Campaign::class)->create();
        Storage::disk('local')->put($campaign->list_path_local,'');
        $file_path = storage_path() . '/app/' . $campaign->list_path_local;
        $file = Writer::createFromPath(new SplFileObject($file_path, 'a+'), 'w');

        // Insert the headers
        $file->insertOne(["phone_number"]);

        $rows = [];
        for ($i=0; $i < 5; $i++) {
            $rows[] = $faker->e164PhoneNumber;
        }

        $file->insertAll($rows);

        // Act
        $jobProcessor = new ProcessCampaignList($campaign);
        $jobProcessor->handle();

        // Assert
        $this->assertEquals(5, $campaign->phoneNumbers()->count());
        $campaign = Campaign::find($campaign->id);
        $this->assertTrue($campaign->list_content_processed);
        $this->assertEquals('ready',$campaign->status);

        //Clean up
        Storage::delete($campaign->list_path_local);
    }

    /** @test */
    public function imports_phone_numbers_in_big_campaign_list()
    {

        //Arrange
        $faker = Faker\Factory::create();

        $campaign = factory(Campaign::class)->create();
        Storage::disk('local')->put($campaign->list_path_local,'');
        $file_path = storage_path() . '/app/' . $campaign->list_path_local;
        $file = Writer::createFromPath(new SplFileObject($file_path, 'a+'), 'w');

        // Insert the headers
        $file->insertOne(["phone_number"]);

        $rows = [];
        for ($i=0; $i < 1003; $i++) {
            $rows[] = $faker->e164PhoneNumber;
        }

        $file->insertAll($rows);

        // Act
        $jobProcessor = new ProcessCampaignList($campaign);
        $jobProcessor->handle();

        // Assert
        $this->assertEquals(1003, $campaign->phoneNumbers()->count());
        $campaign = Campaign::find($campaign->id);
        $this->assertTrue($campaign->list_content_processed);

        //Clean up
        Storage::delete($campaign->list_path_local);
    }
}
