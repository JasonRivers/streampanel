<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDestinationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('relay_id')->unsigned();
            $table->string('name');
            $table->string('address');
            $table->string('external_id')->nullable();
            $table->string('remote_address')->nullable();
            $table->string('encoder')->nullable();
            $table->integer('drops')->default(0);
            $table->timestamp('started_at')->nullable();
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
        Schema::dropIfExists('destinations');
    }
}
