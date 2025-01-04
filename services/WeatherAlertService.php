<?php

namespace App\Services;

use App\Models\Job;
use App\Models\WeatherAlertRecord;
use App\Models\WeatherCondition;
use App\Notifications\WeatherAlertNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class WeatherAlertService
{
    // Checks if the current weather or if any of the forecasts have any of the same weather condition codes as the alert conditions
    public function checkForAlerts(array $weatherData, array $alertConditions, ?Job $job = null): array
    {
        // Preserve original weather data structure while adding alerts
        return [
            'currentWeather' => $weatherData['currentWeather'] ?
                $this->checkForecastForAlert($weatherData['currentWeather'], $alertConditions, $job) :
                null,
            'forecasts' => array_map(
                function ($forecast) use ($alertConditions, $job) {
                    return $forecast ?
                        $this->checkForecastForAlert($forecast, $alertConditions, $job) :
                        null;
                },
                $weatherData['forecasts']
            ),
        ];
    }

    protected function checkForecastForAlert(array $forecast, array $alertConditions, ?Job $job = null): array
    {
        // Ensure we preserve all original weather data
        $forecastWithAlert = $forecast;

        $isRelevantDate = Carbon::parse($forecast['date'])->startOfDay() >= Carbon::now()->startOfDay();

        if (! $isRelevantDate || empty($alertConditions)) {
            $forecastWithAlert['hasAlert'] = false;
            $forecastWithAlert['alert_reasons'] = [];
            return $forecastWithAlert;
        }

        $hasAlert = false;
        $alertReasons = [];

        foreach ($alertConditions as $conditionId) {

            // Get condition details from database
            $condition = WeatherCondition::find($conditionId);

            if (! $condition) {
                continue;
            }

            switch ($condition->alert_condition) {
                case 'frost (0°C eller under)':
                    if ($forecast['min_temp'] <= 0) {
                        $hasAlert = true;
                        $alertReasons[] = 'frost';
                    }
                    break;
                case 'høj varme (over 30°C)':
                    if ($forecast['max_temp'] >= 30) {
                        $hasAlert = true;
                        $alertReasons[] = 'høj varme';
                    }
                    break;
                case 'kraftig vind (over 10 m/s)':
                    $windSpeed = $forecast['wind_speed'];
                    $windGust = $forecast['wind_gust'] ?? null;
                    if ($windSpeed > 10 || $windGust > 12.5) {
                        $hasAlert = true;
                        $alertReasons[] = 'kraftig vind';
                    }
                    break;
                case 'regn':
                    if (($forecast['open_weather_id'] >= 500 && $forecast['open_weather_id'] <= 504) ||
                        ($forecast['open_weather_id'] >= 520 && $forecast['open_weather_id'] <= 531)) {
                        $hasAlert = true;
                        $alertReasons[] = 'regn';
                    }
                    break;
                case 'sne':
                    if (($forecast['open_weather_id'] >= 600 && $forecast['open_weather_id'] <= 602) ||
                        ($forecast['open_weather_id'] >= 611 && $forecast['open_weather_id'] <= 616) ||
                        ($forecast['open_weather_id'] >= 620 && $forecast['open_weather_id'] <= 622)) {
                        $hasAlert = true;
                        $alertReasons[] = 'sne';
                    }
                    break;
                case 'tordenvejr':
                    if (($forecast['open_weather_id'] >= 200 && $forecast['open_weather_id'] <= 202) ||
                        ($forecast['open_weather_id'] >= 210 && $forecast['open_weather_id'] <= 212) ||
                        ($forecast['open_weather_id'] === 221) ||
                        ($forecast['open_weather_id'] >= 230 && $forecast['iopen_weather_id'] <= 232)) {
                        $hasAlert = true;
                        $alertReasons[] = 'tordenvejr';
                    }
                    break;
                case 'tåge':
                    if ($forecast['open_weather_id'] === 741) {
                        $hasAlert = true;
                        $alertReasons[] = 'tåge';
                    }
                    break;
                case 'isslag':
                    if ($forecast['open_weather_id'] === 511) {
                        $hasAlert = true;
                        $alertReasons[] = 'isslag';
                    }

                default:
                    // Your existing OpenWeather ID check
                    if ($forecast['open_weather_id'] === $condition->open_weather_id) {
                        $hasAlert = true;
                        $alertReasons[] = "Weather alert: {$condition->alert_condition}";
                    }
                    break;
            }
          
        }

        $forecastWithAlert['hasAlert'] = $hasAlert;
        $forecastWithAlert['alert_reasons'] = $alertReasons;

        // Send notifications if there's an alert
        if ($hasAlert && $job) {
            $this->sendNotifications($job, $forecastWithAlert);
        }

        return $forecastWithAlert;
    }

    protected function sendNotifications(Job $job, array $alertForecast)
    {
        $job->load('users');

        foreach ($job->users as $user) {
            try {
                foreach ($alertForecast['alert_reasons'] as $reason) {
                WeatherAlertRecord::create([
                    'user_id' => $user->id,
                    'job_id' => $job->id,
                    'alert_date' => $alertForecast['date'],
                    'alert_reason' => $reason,
                    'condition' => $alertForecast['description'],
                ]);
                }
                // Only send notification if record was created successfully
                $user->notify(new WeatherAlertNotification($job, $alertForecast));

            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->errorInfo[1] === 1062) { // MySQL error code for duplicate entry
                    continue;
                }
                throw $e;
            }
        }
    }
}
