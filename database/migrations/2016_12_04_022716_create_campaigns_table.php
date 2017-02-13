<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->unsigned();
            $table->string('name',100);
            $table->string('description',500);
            $table->text('message');
            $table->string('locale',10);
            $table->boolean('list_content_processed')->default(0);
            $table->integer('phone_number_count')->default(0);
            $table->string('status',20)->notNull();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->string('list_path_local')->nullable();
            $table->string('list_path_remote')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaigns');
    }
}
