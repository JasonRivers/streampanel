<?php

namespace App;

// Internals
use App\Traits\ModelToString;

use Illuminate\Database\Eloquent\Model;

/**
 * App\RelayState
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Relay[] $relays
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelayState whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelayState whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelayState whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelayState whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\RelayState whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RelayState extends Model
{
    use ModelToString;
    
    const NONE = 'rsNone';
    const ACTIVE = 'rsActive';
    const DISABLED = 'rsDisabled';
    
    public function relays()
    {
        return $this->hasMany(\App\Relay::class);
    }
}
