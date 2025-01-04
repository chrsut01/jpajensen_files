<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class WebhusetSettings extends Settings
{
    public bool $machines_enabled;

    public string $machine_name_singular;

    public string $machine_name_plural;

    public bool $expenses_enabled;

    public bool $most_used_products_enabled;

    public bool $desired_margin_percentage_enabled;

    public bool $weather_enabled;

    public static function group(): string
    {
        return 'webhuset';
    }
}
