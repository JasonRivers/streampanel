<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTwitchChannelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitch_channels', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('relay_id')->unsigned();
            $table->string('name');
            $table->string('title')->nullable();
            $table->integer('viewers')->default(0);
            $table->boolean('active')->default(false);
            $table->string('thumbnail')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
            
            $table->foreign('relay_id')->references('id')->on('relays')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twitch_channels');
    }
}
