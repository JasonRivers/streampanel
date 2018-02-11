<?php

namespace App;

// Internals
use App\Traits\LoggableModel;
use App\Traits\ModelToString;

// Externals
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * App\TwitchChannel
 *
 * @property int $id
 * @property int $relay_id
 * @property string $name
 * @property string|null $title
 * @property int $viewers
 * @property int $active
 * @property string|null $thumbnail
 * @property \Carbon\Carbon|null $last_updated_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Relay $relay
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitchChannel whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitchChannel whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitchChannel whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitchChannel whereLastUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitchChannel whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitchChannel whereRelayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitchChannel whereThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitchChannel whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitchChannel whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\TwitchChannel whereViewers($value)
 * @mixin \Eloquent
 */
class TwitchChannel extends Model
{
    use ModelToString;
    use LoggableModel;
    
    protected $dates = [
        'created_at',
        'updated_at',
        'last_updated_at'
    ];
    
    public function relay()
    {
        return $this->belongsTo(\App\Relay::class);
    }
    
    public function updateFromTwitch()
    {
        $this->log()->info("Getting information from Twitch for {$this->name}");
        $this->last_updated_at = Carbon::now();
        $api = resolve('twitchapi');
        $user = $api->getUserByUsername($this->name);
        $userId = $user['users'][0]['_id'];
        $stream = $api->getLiveStreams($userId);
        if (!isset($stream['streams'][0])) {
            $this->log()->debug('Twitch channel is not online');
            $this->viewers = 0;
            $this->active = false;
            $this->thumbnail = null;
            $this->title = null;
            $this->save();
            return;
        }
        
        $channel = $stream['streams'][0];
        $this->viewers = $channel['viewers'];
        $this->log()->debug("Twitch is online with {$this->viewers} viewers");
        $this->active = true;
        $this->thumbnail = $channel['preview']['template'];
        $this->title = $channel['channel']['status'];
        $this->save();
    }
}
