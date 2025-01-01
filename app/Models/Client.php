<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'facebook_id',
        'name',
        'profile_pic',
        'locale',
        'last_interaction'
    ];

    public function chatHistory()
    {
        return $this->hasMany(ChatHistory::class, 'user_id', 'facebook_id');
    }

    public static function findOrCreateFromFacebook($facebookId)
    {
        $client = self::firstOrNew(['facebook_id' => $facebookId]);
        
        if (!$client->exists) {
            // Fetch user info from Facebook
            $client->save();
        }

        $client->last_interaction = now();
        $client->save();

        return $client;
    }
}
