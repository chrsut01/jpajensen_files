<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherForecastService
{
    protected $apiKey;

    protected WeatherTranslator $translator;

    protected const CACHE_TTL = 60 * 10; // 10 minutes in seconds

    public function __construct()
    {
        $this->apiKey = env('VITE_OPENWEATHER_API_KEY');
        $this->translator = new WeatherTranslator;
    }

    /**
     * Get weather forecasts for multiple dates
     */
    public function getForecasts($lat, $lng, $startDate, $endDate)
    {
        $period = CarbonPeriod::create(
            Carbon::parse($startDate), // Keep original time
            '1 day',                   // Step by day
            Carbon::parse($endDate)    // Keep original time
        )->setTimezone('UTC');

        $forecasts = [];

        // Iterate over each date in the period
        foreach ($period as $date) {
            $forecast = $this->getForecastFromCache($lat, $lng, $date);
            if ($forecast) {
                $forecasts[] = [
                    'date' => $date->toISOString(),
                    'icon' => $forecast['icon'],
                    'description' => $forecast['description'],
                    'open_weather_id' => $forecast['open_weather_id'],
                    'main' => $forecast['main'],
                    'min_temp' => $forecast['min_temp'],
                    'max_temp' => $forecast['max_temp'],
                    'wind_speed' => $forecast['wind_speed'],
                    'wind_gust' => $forecast['wind_gust'] ?? null,
                ];
            }
        }

        // Return both first day weather and all forecasts
        return [
            'currentWeather' => $forecasts[0] ?? null,
            'forecasts' => $forecasts,
        ];
    }

    // Get weather data from cache if available (from 10 minutes ago), otherwise fetch new data (fetchOpenWeatherData) and cache it
    public function getForecastFromCache($lat, $lng, $dateTime)
    {
        $cacheKey = $this->generateCacheKey($lat, $lng, $dateTime);

         // If cached weather ($cachekey) has not expired, return it, otherwise fetch new data (self::CACHE_TTL, function ())
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($lat, $lng, $dateTime) {
            return $this->fetchOpenWeatherData($lat, $lng, $dateTime);
        });
    }

    protected function generateCacheKey($lat, $lng, $dateTime): string
    {
        // Round coordinates to 4 decimal places to ensure consistent cache keys
        $lat = round($lat, 4);
        $lng = round($lng, 4);

        // Keep full datetime in cache key
        $date = $dateTime instanceof Carbon ? $dateTime : Carbon::parse($dateTime);

        return "weather_data:{$lat}:{$lng}:".$date->toISOString();
    }

    protected function fetchOpenWeatherData($lat, $lng, $dateTime)
    {
            $response = Http::get('https://api.openweathermap.org/data/3.0/onecall', [
                'lat' => $lat,
                'lon' => $lng,
                'exclude' => 'current,minutely,hourly,alerts',
                'appid' => $this->apiKey,
                'units' => 'metric',
            ]);

            if (!$response->successful()) {
                return null;
            }

       $forecast = $response->json('daily');
        $daysDiff = now()->diffInDays($dateTime);

        if (isset($forecast[$daysDiff])) {
            $dayForecast = $forecast[$daysDiff];
            $weather = $dayForecast['weather'][0];

            return [
                'date' => $dateTime->toISOString(),
                'icon' => $weather['icon'],
                'description' => $this->translator->translateForecast([
                    'id' => $weather['id'],
                    'description' => $weather['description'],
                ])['description'],
                'open_weather_id' => $weather['id'],
                'main' => $weather['main'],
                'min_temp' => $dayForecast['temp']['min'],
                'max_temp' => $dayForecast['temp']['max'],
                'wind_speed' => $dayForecast['wind_speed'],
                'wind_gust' => $dayForecast['wind_gust'] ?? null,
            ];
        }

        return null;
    }
}
