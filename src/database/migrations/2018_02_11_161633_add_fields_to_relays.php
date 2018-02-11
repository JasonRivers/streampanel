<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToRelays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('relays', function (Blueprint $table) {
            $table->string('ip')->after('rancher_id')->nullable();
            $table->timestamp('started_at')->nullable()->after('active');
            $table->integer('out_bitrate')->default(0)->after('active');
            $table->integer('in_bitrate')->default(0)->after('active');
            $table->bigInteger('out_bytes')->default(0)->after('active');
            $table->bigInteger('in_bytes')->default(0)->after('active');
            $table->integer('channels')->default(0)->after('active');
            $table->integer('frequency')->default(0)->after('active');
            $table->integer('audio_bitrate')->default(0)->after('active');
            $table->string('audio_codec')->nullable()->after('active');
            $table->float('fps')->default(0)->after('active');
            $table->integer('width')->default(0)->after('active');
            $table->integer('height')->default(0)->after('active');
            $table->integer('video_bitrate')->default(0)->after('active');
            $table->string('video_codec')->nullable()->after('active');
            $table->string('source_address')->nullable()->after('active');
            $table->string('source_encoder')->nullable()->after('active');
            $table->string('source_url')->nullable()->after('active');
            $table->integer('source_drops')->default(0)->after('active');
            $table->timestamp('source_started_at')->nullable()->after('active');
            $table->integer('connections')->default(0)->after('active');
            $table->integer('twitch_viewers')->default(0)->after('active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('relays', function (Blueprint $table) {
            $table->dropColumn([
                'ip',
                'connections',
                'video_codec',
                'video_bitrate',
                'height',
                'width',
                'fps',
                'audio_codec',
                'audio_bitrate',
                'frequency',
                'channels',
                'in_bytes',
                'out_bytes',
                'in_bitrate',
                'out_bitrate',
                'source_address',
                'source_encoder',
                'source_url',
                'source_drops',
                'source_started_at',
                'twitch_viewers'
            ]);
        });
    }
}
