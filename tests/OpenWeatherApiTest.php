<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenWeatherApiTest extends TestCase
{
    //Test if we can connect to OpenWeather API with our key
     
    public function test_can_connect_to_openweather_api(): void
    {
        $apiKey = env('VITE_OPENWEATHER_API_KEY');
        $lat = '54.961144'; // Test coordinates
        $lon = '8.692036';

        $response = Http::get("https://api.openweathermap.org/data/3.0/onecall", [  
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $apiKey,
            'units' => 'metric'
        ]);

        // First check if we get a successful response
        $this->assertTrue($response->successful(), 
            'OpenWeather API request failed. Check if your API key is valid.'
        );

        // Check if we got the expected data structure
        $data = $response->json();
        
        $this->assertArrayHasKey('lat', $data, 
            'Response is missing latitude data'
        );
        $this->assertArrayHasKey('lon', $data, 
            'Response is missing longitude data'
        );
        $this->assertArrayHasKey('timezone', $data, 
            'Response is missing timezone data'
        );
        $this->assertArrayHasKey('current', $data, 
            'Response is missing current weather data'
        );
    }

    //Test with invalid API key to ensure error handling works
     
    public function test_handles_invalid_api_key(): void
    {
        $invalidApiKey = 'invalid_key';
        $lat = '54.961144';
        $lon = '8.692036';

        $response = Http::get("https://api.openweathermap.org/data/3.0/onecall", [
            'lat' => $lat,
            'lon' => $lon,
            'appid' => $invalidApiKey,
            'units' => 'metric'
        ]);

        $this->assertFalse($response->successful());
        $this->assertEquals(401, $response->status());
    }

    
     //Test API rate limiting by making multiple requests
    
    public function test_api_rate_limiting(): void
    {
        $apiKey = env('VITE_OPENWEATHER_API_KEY');
        $lat = '54.961144';
        $lon = '8.692036';

        $successfulRequests = 0;
        
        // Make several requests in quick succession
        for ($i = 0; $i < 5; $i++) {
            $response = Http::get("https://api.openweathermap.org/data/3.0/onecall", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $apiKey,
                'units' => 'metric'
            ]);

            if ($response->successful()) {
                $successfulRequests++;
            }

            // Small delay to avoid hitting rate limit too hard
            usleep(100000); // 100ms delay
        }

        $this->assertEquals(5, $successfulRequests, 
            'Failed to make expected number of successful requests. Check rate limits.'
        );
    }
}
