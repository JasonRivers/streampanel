<?php

namespace App;

// Internals
use App\Traits\LoggableModel;
use App\Traits\ModelToString;

// Externals
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * App\Destination
 *
 * @property int $id
 * @property int $relay_id
 * @property string $name
 * @property int|null $external_id
 * @property string|null $address
 * @property string $remote_address
 * @property string|null $encoder
 * @property int $drops
 * @property string|null $started_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \App\Relay $relay
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Destination whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Destination whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Destination whereDrops($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Destination whereEncoder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Destination whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Destination whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Destination whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Destination whereRelayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Destination whereRemoteAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Destination whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Destination whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Destination extends Model
{
    use ModelToString;
    use LoggableModel;
    
    protected $dates = [
        'created_at',
        'updated_at',
        'started_at'
    ];
    
    public function relay()
    {
        return $this->belongsTo(\App\Relay::class);
    }
    
    public static function createFromRancher(Relay $relay, $url)
    {
        $relay->refresh();
        
        $total = $relay->destinations()->count();
        $dest = $relay->destinations()->whereAddress($url)->first();
        if (!$dest) {
            $dest = new Destination;
            $dest->relay()->associate($relay);
            $dest->name = 'Output ' . ($total + 1);
            $dest->address = $url;
            $dest->save();
            $dest->log()->info("{$relay} Created new destination for {$url}");
        }
        return $dest;
    }
    
    public function updateFromNginx($data = null)
    {
        if (!$data) {
            $this->external_id = null;
            $this->remote_address = null;
            $this->drops = 0;
            $this->encoder = null;
            $this->started_at = null;
        } else {
            $this->external_id = (string) $data->id;
            $this->remote_address = (string) $data->address;
            $this->encoder = (string) $data->flashver;
            $this->drops = (int) $data->dropped;
            $this->started_at = Carbon::now()->subSeconds(((int) $data->time) / 1000);
        }
    }
}
