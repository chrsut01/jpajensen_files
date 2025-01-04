<?php

namespace App\Services;

class WeatherTranslator
{
    private array $weatherCodes = [
        // Group 2xx: Thunderstorm
        200 => ['description' => 'thunderstorm with light rain', 'danish' => 'tordenvejr med let regn'],
        201 => ['description' => 'thunderstorm with rain', 'danish' => 'tordenvejr med regn'],
        202 => ['description' => 'thunderstorm with heavy rain', 'danish' => 'tordenvejr med kraftig regn'],
        210 => ['description' => 'light thunderstorm', 'danish' => 'let tordenvejr'],
        211 => ['description' => 'thunderstorm', 'danish' => 'tordenvejr'],
        212 => ['description' => 'heavy thunderstorm', 'danish' => 'kraftigt tordenvejr'],
        221 => ['description' => 'ragged thunderstorm', 'danish' => 'ustadigt tordenvejr'],
        230 => ['description' => 'thunderstorm with light drizzle', 'danish' => 'tordenvejr med let støvregn'],
        231 => ['description' => 'thunderstorm with drizzle', 'danish' => 'tordenvejr med støvregn'],
        232 => ['description' => 'thunderstorm with heavy drizzle', 'danish' => 'tordenvejr med kraftig støvregn'],

        // Group 3xx: Drizzle
        300 => ['description' => 'light intensity drizzle', 'danish' => 'let støvregn'],
        301 => ['description' => 'drizzle', 'danish' => 'støvregn'],
        302 => ['description' => 'heavy intensity drizzle', 'danish' => 'kraftig støvregn'],
        310 => ['description' => 'light intensity drizzle rain', 'danish' => 'let støvregn'],
        311 => ['description' => 'drizzle rain', 'danish' => 'støvregn'],
        312 => ['description' => 'heavy intensity drizzle rain', 'danish' => 'kraftig støvregn'],
        313 => ['description' => 'shower rain and drizzle', 'danish' => 'regnbyger og støvregn'],
        314 => ['description' => 'heavy shower rain and drizzle', 'danish' => 'kraftige regnbyger og støvregn'],
        321 => ['description' => 'shower drizzle', 'danish' => 'byger med støvregn'],

        // Group 5xx: Rain
        500 => ['description' => 'light rain', 'danish' => 'let regn'],
        501 => ['description' => 'moderate rain', 'danish' => 'moderat regn'],
        502 => ['description' => 'heavy intensity rain', 'danish' => 'kraftig regn'],
        503 => ['description' => 'very heavy rain', 'danish' => 'meget kraftig regn'],
        504 => ['description' => 'extreme rain', 'danish' => 'ekstrem regn'],
        511 => ['description' => 'freezing rain', 'danish' => 'isslag'],
        520 => ['description' => 'light intensity shower rain', 'danish' => 'lette regnbyger'],
        521 => ['description' => 'shower rain', 'danish' => 'regnbyger'],
        522 => ['description' => 'heavy intensity shower rain', 'danish' => 'kraftige regnbyger'],
        531 => ['description' => 'ragged shower rain', 'danish' => 'ustadige regnbyger'],

        // Group 6xx: Snow
        600 => ['description' => 'light snow', 'danish' => 'let sne'],
        601 => ['description' => 'snow', 'danish' => 'sne'],
        602 => ['description' => 'heavy snow', 'danish' => 'kraftig sne'],
        611 => ['description' => 'sleet', 'danish' => 'slud'],
        612 => ['description' => 'light shower sleet', 'danish' => 'lette sludbyger'],
        613 => ['description' => 'shower sleet', 'danish' => 'sludbyger'],
        615 => ['description' => 'light rain and snow', 'danish' => 'let regn og sne'],
        616 => ['description' => 'rain and snow', 'danish' => 'regn og sne'],
        620 => ['description' => 'light shower snow', 'danish' => 'lette snebyger'],
        621 => ['description' => 'shower snow', 'danish' => 'snebyger'],
        622 => ['description' => 'heavy shower snow', 'danish' => 'kraftige snebyger'],

        // Group 7xx: Atmosphere
        701 => ['description' => 'mist', 'danish' => 'tåge'],
        711 => ['description' => 'smoke', 'danish' => 'røg'],
        721 => ['description' => 'haze', 'danish' => 'dis'],
        731 => ['description' => 'sand/dust whirls', 'danish' => 'sand-/støvhvirvler'],
        741 => ['description' => 'fog', 'danish' => 'tæt tåge'],
        751 => ['description' => 'sand', 'danish' => 'sand'],
        761 => ['description' => 'dust', 'danish' => 'støv'],
        762 => ['description' => 'volcanic ash', 'danish' => 'vulkansk aske'],
        771 => ['description' => 'squalls', 'danish' => 'vindstød'],
        781 => ['description' => 'tornado', 'danish' => 'tornado'],

        // Group 800: Clear
        800 => ['description' => 'clear sky', 'danish' => 'klar himmel'],

        // Group 80x: Clouds7877
        801 => ['description' => 'few clouds', 'danish' => 'let skyet'],
        802 => ['description' => 'scattered clouds', 'danish' => 'spredte skyer'],
        803 => ['description' => 'broken clouds', 'danish' => 'skyet'],
        804 => ['description' => 'overcast clouds', 'danish' => 'overskyet'],
    ];

    public function translateForecast(array $forecast): array
    {

        // If we have a direct weather code match
        if (isset($forecast['id']) && isset($this->weatherCodes[$forecast['id']])) {
            $forecast['description'] = $this->weatherCodes[$forecast['id']]['danish'];

            return $forecast;
        }

        // Fallback: try to match by description if no ID or ID not found
        if (isset($forecast['description'])) {
            foreach ($this->weatherCodes as $code) {
                if (strtolower($code['description']) === strtolower($forecast['description'])) {
                    $forecast['description'] = $code['danish'];
                    break;
                }
            }
        }

        return $forecast;
    }
}
