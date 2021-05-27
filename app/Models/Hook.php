<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class Hook extends Model
{
    use HasFactory;
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($webhook) {
            $webhook->identifier = Str::random(25);
        });
    }

    /**
     * @param $user_id
     * @return mixed|null
     */
    public static function getIdentifier($webhook)
    {
        return  self::where('identifier', '=', $webhook)->get();
    }
}
