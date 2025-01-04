<?php

namespace App\Providers;

use App\Models\Hour;
use App\Models\InvoiceLine;
use App\Observers\HourObserver;
use App\Observers\InvoiceLineObserver;
use App\Settings\WebhusetSettings;
use Feature;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        InvoiceLine::observe(InvoiceLineObserver::class);
        Hour::observe(HourObserver::class);
        Password::defaults(function () {
            $rule = Password::min(8)->mixedCase()->letters()->numbers();

            return app()->isProduction() ? $rule->uncompromised() : $rule;

        });

        Feature::define('machines', function () {

            return app(WebhusetSettings::class)->machines_enabled === true;
        });
        Feature::define('expenses', function () {

            return app(WebhusetSettings::class)->expenses_enabled === true;
        });
        Feature::define('most_used_products', function () {

            return app(WebhusetSettings::class)->most_used_products_enabled === true;
        });
        Feature::define('desired_margin_percentage', function () {

            return app(WebhusetSettings::class)->desired_margin_percentage_enabled === true;
        });

        Feature::define('weather', function () {

            return app(WebhusetSettings::class)->weather_enabled === true;
        });

        EnsureFeaturesAreActive::whenInactive(
            function (Request $request, array $features) {
                return abort(Response::HTTP_NOT_FOUND);
            }
        );

    }
}
