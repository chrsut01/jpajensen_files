<?php

namespace App\Http\Middleware;

use App\Models\ImageSetting;
use App\Settings\GeneralSettings;
use App\Settings\WebhusetSettings;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Laravel\Pennant\Feature;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'permissions' => $request->user() ? $request->user()->permissions : [],
                'csrf_token' => csrf_token(),
                'notification' => $request->user() ? $request->user()->unreadNotifications : [],
                'permissions_v2' => [
                    'can_see_cost_price' => $request->user()?->can('kan se kostpriser'),
                    'can_see_sales_price' => $request->user()?->can('se salgspris'),
                    'can_see_expenses' => $request->user()?->can('se udgift'),

                ],
            ],
            'search_query' => $request->q ?? '',
            'google_map_api_key' => config('services.google.map_api_key'),
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'status' => fn () => $request->session()->get('status'),
                'newly_created' => fn () => $request->session()->get('newly_created'),
            ],
            'features' => [
                'machines' => [
                    'enabled' => Feature::value('machines'),
                    'machine_name_singular' => app(WebhusetSettings::class)->machine_name_singular,
                    'machine_name_plural' => app(WebhusetSettings::class)->machine_name_plural,
                ],
                'expenses' => [
                    'enabled' => Feature::value('expenses'),
                ],
                'most_used_products' => [
                    'enabled' => Feature::value('most_used_products'),
                ],
                'desired_margin_percentage' => [
                    'enabled' => Feature::value('desired_margin_percentage'),
                ],
                'weather' => [
                    'enabled' => Feature::value('weather'),
                ],
            ],
            'site_logo' => ImageSetting::where('name', 'logo')->first()?->getFirstMediaUrl('sitelogo') ?? '',
            'menu_color' => app(GeneralSettings::class)->menu_color,
            'menu_accentcolor' => app(GeneralSettings::class)->menu_accentcolor,
            'menu_textcolor' => app(GeneralSettings::class)->menu_textcolor,
        ];
    }
}
