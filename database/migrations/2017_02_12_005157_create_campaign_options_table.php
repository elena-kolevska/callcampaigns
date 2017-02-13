<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCampaignOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_options', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('campaign_id')->unsigned();
            $table->foreign('campaign_id')->references('id')->on('campaigns')->onDelete('cascade');;
            $table->string('digit',20)->default('');
            $table->integer('count')->default(0);
            $table->string('label', 100)->default('');
            $table->string('message', 300)->default('');
            $table->string('thank_you_message', 500)->default('');
            $table->string('exported_list_url',5)->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('campaign_options');
    }
}
