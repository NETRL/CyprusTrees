<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeocodeController extends Controller
{

    public function search(Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'min:3'],
        ]);

        $q = trim($request->q);
        $countryCodes = $request->string('countrycodes')->toString();

        $cacheKey = 'geocode:search:' . md5($q . '|' . $countryCodes);

        return Cache::remember($cacheKey, now()->addDays(3), function () use ($q, $countryCodes) {

            // global 1 req/sec protection
            Cache::lock('nominatim:search:rate', 1)->block(5, function () {
                usleep(1_000_000);
            });

            $response = Http::withHeaders([
                'User-Agent' => 'CyprusTrees/1.0 (smartParking.platform.info@gmail.com)',
                'From'       => 'contact@YOURDOMAIN',
                'Accept'     => 'application/json',
            ])
                ->timeout(10)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'format'         => 'jsonv2',
                    'q'              => $q,
                    'addressdetails' => 1,
                    'limit'          => 10,
                    'countrycodes'   => $countryCodes ?: null,
                ]);

            if (!$response->successful()) {
                abort(502, 'Search geocoding failed');
            }

            return $response->json();
        });
    }


    public function reverse(Request $request)
    {
        $request->validate([
            'lat' => ['required', 'numeric'],
            'lon' => ['required', 'numeric'],
        ]);

        // Normalize to avoid cache fragmentation
        $lat = round((float) $request->lat, 6);
        $lon = round((float) $request->lon, 6);

        $cacheKey = "geocode:reverse:{$lat}:{$lon}";

        return Cache::remember(
            $cacheKey,
            now()->addDays(7),
            function () use ($lat, $lon) {
                $response = Http::withHeaders([
                    'User-Agent' => 'CyprusTrees/1.0 (smartParking.platform.info@gmail.com)',
                    'Accept'     => 'application/json',
                ])
                    ->timeout(10)
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'format'         => 'jsonv2',
                        'lat'            => $lat,
                        'lon'            => $lon,
                        'zoom'           => 18,
                        'addressdetails' => 1,
                    ]);

                if (!$response->successful()) {
                    abort(502, 'Reverse geocoding failed');
                }

                return $response->json();
            }
        );
    }
}
