<?php

namespace Tests\Feature;

use App\Models\Job;
use App\Models\User;
use App\Models\WeatherCondition;
use App\Models\WeatherAlertRecord;
use App\Notifications\WeatherAlertNotification;
use App\Services\WeatherForecastService;
use App\Services\WeatherAlertService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WeatherAlertTest extends TestCase
{
    use RefreshDatabase;

    private WeatherAlertService $weatherAlertService;
    private WeatherForecastService $weatherForecastService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->weatherAlertService = new WeatherAlertService();
        $this->weatherForecastService = new WeatherForecastService();
    }

 

    /** @test */
    public function detects_alert_when_weather_matches_conditions()
    {
        $condition = WeatherCondition::create([
            'alert_condition' => 'sne',
            'open_weather_id' => 600
        ]);

        $weatherData = [
            'currentWeather' => null,
            'forecasts' => [[
                'date' => now()->addDay()->toISOString(),
                'open_weather_id' => 600,
                'description' => 'snow',
                'min_temp' => 0,
                'max_temp' => 2,
                'wind_speed' => 5,
            ]]
        ];

        $result = $this->weatherAlertService->checkForAlerts($weatherData, [$condition->id], null);

        $this->assertTrue($result['forecasts'][0]['hasAlert']);
        $this->assertContains('sne', $result['forecasts'][0]['alert_reasons']);
    }
}
