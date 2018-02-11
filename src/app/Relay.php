<?php

namespace App;

// Internals
use App\Traits\ModelToString;
use App\Traits\LoggableModel;
use App\Jobs\RelayDisableJob;
use App\Jobs\RelayEnableJob;
use App\Jobs\RelayUpdateJob;
use App\Jobs\RelayUpdateRancherJob;

// Externals
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use Carbon\Carbon;

// Facades
use Rancher;
use DB;

/**
 * App\Relay
 *
 * @property int $id
 * @property string $name
 * @property string|null $rancher_id
 * @property int|null $http_port
 * @property int|null $rtmp_port
 * @property string|null $key
 * @property int $active
 * @property string|null $last_updated_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereHttpPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereLastUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereRancherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereRtmpPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int $state_id
 * @property string|null $ip
 * @property string|null $source_started_at
 * @property int $source_drops
 * @property string|null $source_url
 * @property string|null $source_encoder
 * @property string|null $source_address
 * @property string|null $video_codec
 * @property int $video_bitrate
 * @property int $height
 * @property int $width
 * @property float $fps
 * @property string|null $audio_codec
 * @property int $audio_bitrate
 * @property int $frequency
 * @property int $channels
 * @property int $in_bytes
 * @property int $out_bytes
 * @property int $in_bitrate
 * @property int $out_bitrate
 * @property string|null $started_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Destination[] $destinations
 * @property-read \App\RelayState $state
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereAudioBitrate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereAudioCodec($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereChannels($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereFps($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereFrequency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereInBitrate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereInBytes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereOutBitrate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereOutBytes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereSourceAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereSourceDrops($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereSourceEncoder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereSourceStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereSourceUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereVideoBitrate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereVideoCodec($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereWidth($value)
 * @property int $connections
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereConnections($value)
 * @property int $twitch_viewers
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\TwitchChannel[] $twitchChannels
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Relay whereTwitchViewers($value)
 */
class Relay extends Model
{
    use ModelToString;
    use LoggableModel;
    
    protected $dates = [
        'created_at',
        'updated_at',
        'source_started_at',
        'started_at',
        'last_updated_at'
    ];
    
    public function state()
    {
        return $this->belongsTo(\App\RelayState::class);
    }
    
    public function destinations()
    {
        return $this->hasMany(\App\Destination::class);
    }
    
    public function twitchChannels()
    {
        return $this->hasMany('App\TwitchChannel');
    }
    
    public function setState($code)
    {
        $this->log()->info("Updating state to {$code}");
        $state = RelayState::whereCode($code)->first();
        $this->state()->associate($state);
    }
    
    public function setInitialProperties()
    {
        if (!$this->state) {
            $this->setState('rsNone');
        }
    }
    
    public function updateStatus($async = false)
    {
        if ($async) {
            $this->log()->info('Queueing status update');
            RelayUpdateJob::dispatch($this);
            return;
        }
        
        $this->log()->info('Updating state');
        DB::beginTransaction();
        $this->updateFromRancher();
        $this->updateFromNginx();
        $this->updateFromTwitch();
        $this->last_updated_at = Carbon::now();
        $this->save();
        DB::commit();
    }
    
    public function updateFromRancher()
    {
        $this->log()->info('Updating from Rancher');
        if (!$this->rancher_id) {
            $this->log()->notice('No Rancher ID specified');
            return;
        }
        $service = Rancher::service()->get($this->rancher_id);
        $this->populateFromRancher($service);
    }
    
    public function updateFromNginx()
    {
        $this->log()->info('Updating from Nginx');
        
        if (!$this->ip || !$this->http_port) {
            $this->log()->notice('Relay has no nginx details, clearning Nginx data');
            $this->connections = 0;
            $this->started_at = null;
            $this->clearNginxData();
            return;
        }
        
        try {
            $client = new Client;
            $data = $client->get("http://{$this->ip}:{$this->http_port}/stat");
        } catch (\Exception $ex) {
            $this->log()->warning("Unable to fetch nginx data: {$ex->getMessage()}");
            $this->connections = 0;
            $this->started_at = null;
            $this->clearNginxData();
            return;
        }
        
        $xml = simplexml_load_string((string) $data->getBody());
        $this->started_at = Carbon::now()->subSeconds((int) $xml->uptime);
        $this->connections = (int) $xml->naccepted;
        
        $streams = $xml->server->application[0];
        $processed = false;
        foreach ($streams as $key => $stream) {
            if ($key == 'name') {
                continue;
            }
            if ((int) $stream->nclients > 0) {
                $processed = true;
                $this->processNginxStream($stream->stream);
            }
        }
        if (!$processed) {
            $this->clearNginxData();
        }
    }
    
    protected function processNginxStream($stream)
    {
        // Video Stats
        $this->video_codec = (string) $stream->meta->video->codec;
        $this->video_codec .= ' ' . (string) $stream->meta->video->profile;
        $this->video_codec .= ' ' . (string) $stream->meta->video->level;
        $this->video_bitrate = (int) $stream->bw_video;
        $this->height = (int) $stream->meta->video->height;
        $this->width = (int) $stream->meta->video->width;
        $this->fps = (float) $stream->meta->video->frame_rate;
        
        // Audio Stats
        $this->audio_codec = (string) $stream->meta->audio->codec;
        $this->audio_codec .= ' ' . (string) $stream->meta->audio->profile;
        $this->audio_bitrate = (int) $stream->bw_audio;
        $this->frequency = (int) $stream->meta->audio->sample_rate;
        $this->channels = (int) $stream->meta->audio->channels;
        
        // Network Stats
        $this->in_bytes = (int) $stream->bytes_in;
        $this->out_bytes = (int) $stream->bytes_out;
        $this->in_bitrate = (int) $stream->bw_in;
        $this->out_bitrate = (int) $stream->bw_out;
        
        // Clients
        $updatedDestinations = [];
        foreach ($stream->client as $client) {
            if ((bool) $client->publishing) {
                // Update our stats
                $this->source_address = (string) $client->address;
                $this->source_encoder = (string) $client->flashver;
                $this->source_drops = (int) $client->dropped;
                $this->source_url = (string) $client->swfurl;
                $this->source_started_at = Carbon::now()->subSeconds(((int) $client->time) / 1000);
            } else {
                $destination = $this->findDestination((int) $client->id, (string) $client->address);
                if (!$destination) {
                    $this->log()->notice("Unable to find destination for {$client->address}");
                    continue;
                }
                $destination->updateFromNginx($client);
                $destination->save();
                $updatedDestinations[] = $destination->id;
            }
        }
        $destinations = $this->destinations()->whereNotIn('id', $updatedDestinations)->get();
        foreach ($destinations as $destination) {
            $destination->updateFromNginx();
            $destination->save();
        }
    }
    
    protected function findDestination($externalId, $url)
    {
        // Match on external ID
        $destination = $this->destinations()->whereExternalId($externalId)->first();
        if ($destination) {
            return $destination;
        }
        
        // Match on remote address
        $destination = $this->destinations()->whereRemoteAddress($url)->first();
        if ($destination) {
            return $destination;
        }
        
        // Match on address
        $destination = $this->destinations()->whereAddress('rtmp://' . $url)->first();
        if ($destination) {
            return $destination;
        }
    }
    
    protected function clearNginxData()
    {
        $this->source_started_at = null;
        $this->source_drops = 0;
        $this->source_url = null;
        $this->source_encoder = null;
        $this->source_address = null;
        $this->video_codec = null;
        $this->video_bitrate = 0;
        $this->height = 0;
        $this->width = 0;
        $this->fps = 0;
        $this->audio_codec = null;
        $this->audio_bitrate = 0;
        $this->frequency = 0;
        $this->channels = 0;
        $this->in_bytes = 0;
        $this->out_bytes = 0;
        $this->in_bitrate = 0;
        $this->out_bitrate = 0;
    }
    
    public function updateFromTwitch()
    {
        $this->log()->info('Updating from Twitch');
        foreach ($this->twitchChannels as $channel) {
            $channel->updateFromTwitch();
        }
        $this->twitch_viewers = $this->twitchChannels()->sum('viewers');
    }
    
    public function enable($async = false)
    {
        if ($async) {
            $this->log()->info('Queueing enable');
            RelayEnableJob::dispatch($this);
            return;
        }
        $this->log()->info('Enabling relay');
        $this->setState(RelayState::ACTIVE);
        Rancher::service()->project(env('RANCHER_STACK'))->update($this->rancher_id, [
            'scale' => 1
        ]);
        $this->active = true;
        $this->log()->info('Enabled');
        $this->save();
    }
    
    public function disable($async = false)
    {
        if ($async) {
            $this->log()->info('Queueing disable');
            RelayDisableJob::dispatch($this);
            return;
        }
        
        $this->log()->info('Disabling relay');
        $this->setState(RelayState::DISABLED);
        Rancher::service()->project(env('RANCHER_STACK'))->update($this->rancher_id, [
            'scale' => 0
        ]);
        $this->active = false;
        $this->log()->info('Disabled');
    }
    
    public function updateRancher($async = false)
    {
        if ($async) {
            $this->log()->info('Queueing Rancher update');
            RelayUpdateRancherJob::dispatch($this);
            return;
        }
        $this->log()->info('Updating Rancher');
        $addresses = [];
        foreach ($this->destinations as $destination) {
            $addresses[] = $destination->address;
        }
        $env = [
            'RTMP_CHANNEL_NAME' => $this->key,
            'RTMP_URLS' => implode('|', $addresses),
            'RTMP_LOCAL_URL' => '',
            'RTMP_REMOTE_URL' => ''
        ];
        if (isset($addresses[0])) {
            $env['RTMP_REMOTE_URL'] = $addresses[0];
        }
        if (isset($addresses[1])) {
            $env['RTMP_LOCAL_URL'] = $addresses[1];
        }
        
        $service = Rancher::service()->get($this->rancher_id);
        $launchConfig = $service->launchConfig;
        
        $current = (array) $launchConfig->environment;
        $diff = array_diff_assoc($env, $current);
        if (!$diff) {
            $diff2 = array_diff_assoc($current, $env);
            if (!$diff2) {
                $this->log()->info('Environment is the same, no update required');
                return;
            }
        }
        
        $launchConfig->environment = $env;
        
        $config = [
            'inServiceStrategy' => [
                'batchSize' => 1,
                'intervalMillis' => 2000,
                'startFirst' => false,
                'launchConfig' => $launchConfig
            ]
        ];
        
        $this->log()->debug('Upgrading rancher service');
        Rancher::service()->upgrade($this->rancher_id, $config);
        $timeout = Carbon::now()->addMinutes(2);
        do {
            sleep(5);
            $this->log()->debug('Checking if upgrade is complete');
            $service = Rancher::service()->get($this->rancher_id);
            $this->log()->debug("State is {$service->state}");
            if ($service->state == 'upgraded') {
                $this->log()->debug('Finishing upgrade');
                Rancher::service()->finishUpgrade($this->rancher_id);
            }
            if (Carbon::now() > $timeout) {
                $this->log()->debug('Timeout waiting for rancher upgrade to complete');
                break;
            }
        } while ($service->state != 'active');
        $this->log()->info('Rancher updated');
    }
    
    public function populateFromRancher($rancherData)
    {
        // Status
        $this->active = false;
        if ($rancherData->state == 'active' && ($rancherData->scale > 0)) {
            $this->active = true;
        } else {
            return;
        }
        
        // Set the IP
        if (isset($rancherData->publicEndpoints[0])) {
            $this->ip = $rancherData->publicEndpoints[0]->ipAddress;
        }
        
        // Now set RTMP Port and HTTP Port
        foreach ($rancherData->launchConfig->ports as $port) {
            $matches = [];
            $details = preg_match('/^(?<external>\d+):(?<internal>\d+)\/(?<protocol>tcp|udp)$/', $port, $matches);
            if (isset($matches['internal'])) {
                if ($matches['internal'] == 8080) {
                    $this->http_port = $matches['external'];
                } elseif ($matches['internal'] == 1935) {
                    $this->rtmp_port = $matches['external'];
                }
            }
        }
        
        // RTMP Channel Name
        foreach ($rancherData->launchConfig->environment as $key => $value) {
            if ($key == 'RTMP_CHANNEL_NAME') {
                $this->key = $value;
            }
        }
    }
    
    public static function pullFromRancher()
    {
        $filter = [
            'stackId' => env('RANCHER_STACK')
        ];
        $services = Rancher::service()->filter($filter)->all();
        foreach ($services as $service) {
            $relay = Relay::where('rancher_id', $service->id)->first();
            if (!$relay) {
                $relay = Relay::createFromRancher($service);
            }
            $relay->updateStatus();
        }
    }
    
    public static function createFromRancher($service)
    {
        $relay = new Relay;
        $relay->rancher_id = $service->id;
        $relay->name = $service->name;
        $relay->save();
        
        $relay->log()->info("Created new relay from Rancher");
        
        // Normally we're authoritative other these, but this is new, so let's pull from rancher
        if ($service->state == 'active') {
            $relay->setState(RelayState::ACTIVE);
        } else {
            $relay->setState(RelayState::DISABLED);
        }
        
        // Also now create our destination streams
        foreach ($service->launchConfig->environment as $key => $value) {
            switch ($key) {
                // Legacy method
                case 'RTMP_REMOTE_URL':
                case 'RTMP_LOCAL_URL':
                    Destination::createFromRancher($relay, $value);
                    break;
                
                // New method
                case 'RTMP_URLS':
                    $urls = array_filter(explode('|', $value));
                    foreach ($urls as $url) {
                        Destination::createFromRancher($relay, $url);
                    }
                    break;
                default:
                    break;
            }
        }
        
        return $relay;
    }
}
