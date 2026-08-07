<?php

namespace App\Helpers;

class DeviceDetector
{
    public static function isBot(?string $userAgent): bool
    {
        if (!$userAgent) {
            return true;
        }

        return (bool) preg_match(
            '/bot|crawl|spider|slurp|archiver|bingpreview|facebookexternalhit|whatsapp|telegram|skypeuripreview|linkpreview|curl|wget|python-requests|httpclient|headlesschrome|lighthouse|gtmetrix|pingdom|uptimerobot|statuscake|scanner|scrapy|libwww|java\//i',
            $userAgent
        );
    }

    /**
     * @return array{device_type: string, browser: string, os: string}
     */
    public static function detect(?string $userAgent): array
    {
        $ua = (string) $userAgent;

        if (preg_match('/ipad|tablet|kindle|silk|playbook/i', $ua)
            || (stripos($ua, 'android') !== false && stripos($ua, 'mobile') === false)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/mobile|iphone|ipod|android|windows phone|opera mini|blackberry/i', $ua)) {
            $deviceType = 'mobile';
        } else {
            $deviceType = 'desktop';
        }

        if (stripos($ua, 'Edg') !== false) {
            $browser = 'Edge';
        } elseif (stripos($ua, 'OPR') !== false || stripos($ua, 'Opera') !== false) {
            $browser = 'Opera';
        } elseif (stripos($ua, 'SamsungBrowser') !== false) {
            $browser = 'Samsung Internet';
        } elseif (stripos($ua, 'MSIE') !== false || stripos($ua, 'Trident') !== false) {
            $browser = 'Internet Explorer';
        } elseif (stripos($ua, 'Firefox') !== false || stripos($ua, 'FxiOS') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($ua, 'CriOS') !== false || stripos($ua, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'Safari') !== false) {
            $browser = 'Safari';
        } else {
            $browser = 'Other';
        }

        if (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $os = 'iOS';
        } elseif (stripos($ua, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($ua, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (stripos($ua, 'Mac OS X') !== false || stripos($ua, 'Macintosh') !== false) {
            $os = 'macOS';
        } elseif (stripos($ua, 'Linux') !== false) {
            $os = 'Linux';
        } else {
            $os = 'Other';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'os' => $os,
        ];
    }
}
