<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassRoom;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GeolocationController extends Controller
{
    public function validateLocation(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'class_id' => 'required|exists:class_rooms,id',
            'accuracy' => 'nullable|numeric|min:0', // GPS accuracy in meters
        ]);

        $class = ClassRoom::findOrFail($data['class_id']);
        
        $distance = $this->calculateDistanceMeters(
            $data['latitude'], 
            $data['longitude'], 
            (float) $class->location_latitude, 
            (float) $class->location_longitude
        );

        $allowedRadius = (int) env('ATTENDANCE_RADIUS_METERS', 50);
        $isWithinRadius = $distance <= $allowedRadius;

        // Consider GPS accuracy in validation
        $gpsAccuracy = $data['accuracy'] ?? 0;
        $effectiveDistance = max(0, $distance - $gpsAccuracy);
        $isValidWithAccuracy = $effectiveDistance <= $allowedRadius;

        return response()->json([
            'is_valid' => $isWithinRadius,
            'is_valid_with_accuracy' => $isValidWithAccuracy,
            'distance_meters' => round($distance, 2),
            'allowed_radius_meters' => $allowedRadius,
            'gps_accuracy_meters' => $gpsAccuracy,
            'effective_distance_meters' => round($effectiveDistance, 2),
            'class_location' => [
                'latitude' => $class->location_latitude,
                'longitude' => $class->location_longitude,
                'name' => $class->name
            ],
            'user_location' => [
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude']
            ]
        ]);
    }

    public function getNearbyClasses(Request $request): JsonResponse
    {
        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_km' => 'nullable|numeric|min:0.1|max:10', // Search radius in kilometers
        ]);

        $searchRadius = $data['radius_km'] ?? 1; // Default 1km
        $userLat = $data['latitude'];
        $userLng = $data['longitude'];

        $classes = ClassRoom::all()->map(function ($class) use ($userLat, $userLng) {
            $distance = $this->calculateDistanceMeters(
                $userLat, $userLng,
                (float) $class->location_latitude,
                (float) $class->location_longitude
            );

            $class->distance_meters = round($distance, 2);
            $class->distance_km = round($distance / 1000, 3);
            
            return $class;
        })->filter(function ($class) use ($searchRadius) {
            return $class->distance_km <= $searchRadius;
        })->sortBy('distance_meters')->values();

        return response()->json([
            'user_location' => [
                'latitude' => $userLat,
                'longitude' => $userLng
            ],
            'search_radius_km' => $searchRadius,
            'classes_found' => $classes->count(),
            'classes' => $classes
        ]);
    }

    private function calculateDistanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000; // meters
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat / 2) ** 2 + 
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * 
             sin($dLng / 2) ** 2;
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    public function getLocationPermissions(): JsonResponse
    {
        return response()->json([
            'required_permissions' => [
                'geolocation' => 'Required for attendance validation',
                'camera' => 'Required for QR code scanning'
            ],
            'browser_requirements' => [
                'https' => 'HTTPS is required for geolocation access in production',
                'modern_browser' => 'Modern browser with geolocation API support'
            ],
            'accuracy_requirements' => [
                'minimum_accuracy_meters' => 100,
                'preferred_accuracy_meters' => 10,
                'timeout_seconds' => 30
            ]
        ]);
    }
}