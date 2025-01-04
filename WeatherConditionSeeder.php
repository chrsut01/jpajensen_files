<?php

namespace Database\Seeders;

use App\Models\WeatherCondition;
use Illuminate\Database\Seeder;

class WeatherConditionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conditions = [

            [
                'open_weather_id' => 500,
                'alert_condition' => 'regn',
                'icon' => '10d',
            ],
            [
                'open_weather_id' => 200,
                'alert_condition' => 'tordenvejr',
                'icon' => '11d',
            ],

            [
                'open_weather_id' => 600,
                'alert_condition' => 'sne',
                'icon' => '13d',
            ],

            [
                'open_weather_id' => 511,  // Specific ID for freezing rain
                'alert_condition' => 'isslag',
                'icon' => '13d',
            ],
            [
                'open_weather_id' => null,
                'alert_condition' => 'frost (0°C eller under)',
                'icon' => '13d',
            ],
            [
                'open_weather_id' => null,
                'alert_condition' => 'høj varme (over 30°C)',
                'icon' => '01d',
            ],
            [
                'open_weather_id' => null,
                'alert_condition' => 'kraftig vind (over 10 m/s)',
                'icon' => '50d',
            ],
            [
                'open_weather_id' => 741,
                'alert_condition' => 'tåge',
                'icon' => '50d',
            ],

        ];

        foreach ($conditions as $condition) {
            WeatherCondition::create($condition);
        }
    }
}
