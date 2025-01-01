<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomingMessage extends Model
{
    protected $fillable = [
        'facebook_id',
        'message',
        'status'
    ];

    public static function getPendingMessages($facebookId)
    {
        return self::where('facebook_id', $facebookId)
            ->where('status', 'unprocessed')
            ->orderBy('created_at')
            ->get();
    }
}
