<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Services\WeatherAlertService;
use App\Services\WeatherForecastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WeatherForecastController extends Controller
{
    protected $weatherForecastService;

    protected $weatherAlertService;  // Uncomment this

    public function __construct(
        WeatherForecastService $weatherForecastService,
        WeatherAlertService $weatherAlertService  // Add parameter
    ) {

        $this->weatherForecastService = $weatherForecastService;
        $this->weatherAlertService = $weatherAlertService;

    }

    public function getWeatherFromService(Request $request)
    {
        try {

            $validated = $request->validate([
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'job_id' => 'nullable|exists:jobs,id',
                // '',
            ]);

            $weatherAlertConditions = [];

            $job = null;

            // Get weather forecasts first
            $weatherData = $this->weatherForecastService->getForecasts(
                $validated['lat'],
                $validated['lng'],
                $validated['start_date'],
                $validated['end_date']
            );

            // Only try to get job data if job_id exists in the request
            if ($request->has('job_id') && $request->job_id) {
                $job = Job::with(['weatherConditions' => function ($query) {
                    $query->select('weather_conditions.*', 'weather_conditions.id as condition_id');
                }])->findOrFail($request->job_id);
                $weatherAlertConditions = $job->weatherConditions->pluck('id')->toArray();
            }

            // Check for alerts
            $weatherDataWithAlerts = $this->weatherAlertService->checkForAlerts(
                $weatherData,
                $weatherAlertConditions,
                $job
            );

            return response()->json($weatherDataWithAlerts);

        } catch (\Exception $e) {
      
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
