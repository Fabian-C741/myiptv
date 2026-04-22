<?php
namespace App\Services;

use App\Models\User;
use App\Models\Device;
use App\Models\DeviceSession;

class TokenService
{
    public function createDeviceToken(User $user, Device $device, $profileId = null)
    {
        $token = $user->createToken('device_' . $device->id);
        
        // Remove old sessions for this exact device to avoid duplicates
        DeviceSession::where('device_id', $device->id)->delete();
        
        DeviceSession::create([
            'user_id' => $user->id,
            'profile_id' => $profileId,
            'device_id' => $device->id,
            'token' => hash('sha256', explode('|', $token->plainTextToken)[1] ?? $token->plainTextToken),
            'expires_at' => now()->addMonths(6)
        ]);

        return $token->plainTextToken;
    }
}
