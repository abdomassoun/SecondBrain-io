<?php

namespace App\Application\Users\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Hash;


class OtpService
{
    public function generateOtp(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function storeOtp(string $email, string $otp, int $ttl = 300): void
    {
        $key = "password_reset:{$email}";

        Redis::setex($key, $ttl, Hash::make($otp));
    }

    public function verifyOtp(string $email, string $otp): bool
    {
        $key = "password_reset:{$email}";
        $hashedOtp = Redis::get($key);

        if (!$hashedOtp) {
            return false;
        }

        return Hash::check($otp, $hashedOtp);
    }

    public function deleteOtp(string $email): void
    {
        Redis::del("password_reset:{$email}");
    }

}
