<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relays', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('rancher_id')->nullable();
            $table->integer('http_port')->nullable();
            $table->integer('rtmp_port')->nullable();
            $table->string('key')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamp('last_updated_at')->nullable()->default(null);
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
        Schema::dropIfExists('relays');
    }
}
