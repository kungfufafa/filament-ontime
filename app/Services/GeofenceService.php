<?php

namespace App\Services;

class GeofenceService
{
    /**
     * Calculate Haversine distance between two coordinates in meters.
     */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Earth radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Validate if coordinates fall within maxRadiusMeters of center location.
     */
    public static function isWithinGeofence(
        float $centerLat,
        float $centerLon,
        float $userLat,
        float $userLon,
        int $maxRadiusMeters
    ): bool {
        $distance = static::calculateDistance($centerLat, $centerLon, $userLat, $userLon);

        return $distance <= $maxRadiusMeters;
    }
}
