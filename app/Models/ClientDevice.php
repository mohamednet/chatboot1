<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientDevice extends Model
{
    protected $fillable = [
        'client_facebook_id',
        'device_type',
        'player_type',
        'login',
        'password',
        'trial_started_at',
        'trial_ends_at',
        'is_active'
    ];

    protected $dates = [
        'trial_started_at',
        'trial_ends_at'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_facebook_id', 'facebook_id');
    }

    public function generateCredentials()
    {
        $this->login = 'user_' . uniqid();
        $this->password = substr(md5(rand()), 0, 8);
        $this->trial_started_at = now();
        $this->trial_ends_at = now()->addDays(3); // 3-day trial
        $this->save();

        return [
            'login' => $this->login,
            'password' => $this->password,
            'expires' => $this->trial_ends_at
        ];
    }

    public static function getActiveDeviceCount($clientFacebookId)
    {
        return self::where('client_facebook_id', $clientFacebookId)
                  ->where('is_active', true)
                  ->count();
    }
}
