<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRelayStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('relay_states', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->timestamps();
        });
        
        Schema::table('relays', function (Blueprint $table) {
            $table->integer('state_id')->after('id')->unsigned();
            
            $table->foreign('state_id')->references('id')->on('relay_states');
        });
        
        $noneState = $this->addState('rsNone', 'None');
        $this->addState('rsActive', 'Active');
        $this->addState('rsDisabled', 'Disabled');
        
        App\Relay::chunk(100, function($chunk) use ($noneState) {
            foreach ($chunk as $relay) {
                $relay->state()->associate($noneState);
                $relay->save();
            }
        });
    }
    
    protected function addState($code, $name)
    {
        $state = App\RelayState::whereCode($code)->first();
        if (!$state) {
            $state = new App\RelayState;
            $state->code = $code;
        }
        $state->name = $name;
        $state->save();
        return $state;
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('relays', function (Blueprint $table) {
            $table->dropForeign('relays_state_id_foreign');
            $table->dropColumn('state_id');
        });
        Schema::dropIfExists('relay_states');
    }
}
