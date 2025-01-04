<?php

namespace App\Services;

use App\Models\WeatherCondition;
use Illuminate\Support\Facades\Log;

class WeatherConditionService
{
    public function getWeatherConditions()
    {
        $conditions = WeatherCondition::orderBy('alert_condition')->get();
        return $conditions;
    }
}
