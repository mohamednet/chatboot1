<?php

namespace App\Services;

use App\Models\ClientDevice;

class DeviceService
{
    const DEVICE_TYPES = [
        'android' => [
            'preferred_app' => '8kvip',
            'install_code' => '439873',
            'fallback_app' => 'iboplayer',
            'fallback_code' => '597218'
        ],
        'smart_tv' => [
            'preferred_app' => 'iboplayer',
            'players' => ['iboplayer', 'smarters', 'tivimate']
        ],
        'ios' => [
            'preferred_app' => 'iboplayer',
            'players' => ['iboplayer', 'smarters', 'tivimate']
        ],
        'roku' => [
            'preferred_app' => 'iboplayer',
            'players' => ['iboplayer', 'smarters']
        ]
    ];

    public function handleDeviceSetup($deviceType, $clientFacebookId)
    {
        $device = new ClientDevice([
            'client_facebook_id' => $clientFacebookId,
            'device_type' => $deviceType
        ]);

        $deviceInfo = self::DEVICE_TYPES[strtolower($deviceType)] ?? self::DEVICE_TYPES['android'];
        $device->player_type = $deviceInfo['preferred_app'];
        $device->save();

        return [
            'device' => $device,
            'setup_info' => $deviceInfo,
            'active_devices' => ClientDevice::getActiveDeviceCount($clientFacebookId)
        ];
    }

    public function generateTrialCredentials($clientFacebookId, $deviceType)
    {
        $device = ClientDevice::where('client_facebook_id', $clientFacebookId)
                            ->where('device_type', $deviceType)
                            ->latest()
                            ->first();

        if (!$device) {
            $device = $this->handleDeviceSetup($deviceType, $clientFacebookId)['device'];
        }

        return $device->generateCredentials();
    }

    public function calculatePrice($numberOfDevices, $months)
    {
        $basePrice = match($months) {
            1 => 15,
            3 => 35,
            6 => 49,
            12 => 79,
            default => 15
        };

        $totalPrice = $basePrice;
        
        for ($i = 2; $i <= $numberOfDevices; $i++) {
            $discount = match($i) {
                2 => 0.65, // 35% off
                3 => 0.55, // 45% off
                4 => 0.45, // 55% off
                5 => 0.40, // 60% off
                6 => 0.35, // 65% off
                default => 1
            };
            
            $totalPrice += ($basePrice * $discount);
        }

        return round($totalPrice, 2);
    }
}
