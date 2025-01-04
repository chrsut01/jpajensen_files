<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EconomicAgreementController;
use App\Http\Controllers\EconomicController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\GeofenceController;
use App\Http\Controllers\GlobalSearchController;
use App\Http\Controllers\HourController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceLayoutController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TariffController;
use App\Http\Controllers\TjekIndController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VacationController;
use App\Http\Controllers\WorkTimeController;
use App\Models\EconomicAgreement;
use App\Models\Hour;
use App\Models\Job;
use App\Models\User;
use App\Services\Economic\CustomerService;
use App\Settings\WebhusetSettings;
use CleaniqueCoders\LaravelMediaSecure\LaravelMediaSecure;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;
use App\Http\Controllers\WeatherForecastController;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function (Request $request) {

    $user = Auth::user();
    $userList = null;
    if ($user->can('kan se alle jobs')) {
        $jobs = Job::where('status', '!=', 'Faktureret')->get();
    } else {
        $jobs = $user->jobs->where('status', '!=', 'Faktureret');
    }

    if ($user->can('kan se alle medarbejdere')) {
        $userList = User::select('id', 'name', 'color')->get();
    } else {
        $userList = User::select('id', 'name', 'color')->where('id', $user->id)->get();
    }

    $economic_agreement = EconomicAgreement::all()->select('id', 'name');

    $props = [
        'jobs' => $jobs->load('customer'),
        'activeJobs' => Job::active()->get(),
        'employeeCount' => User::count(),
        'economicAgreements' => $economic_agreement,
        //users id = id and name = name
        'users' => $userList, //User::select('id', 'name', 'color')->get(),
        'hours' => Hour::IsDone()->get(),
    ];

    return Inertia::render('Dashboard', $props);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::inertiaTable();
    LaravelMediaSecure::routes();

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //ROLES
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles/store', [RoleController::class, 'store'])->name('roles.store');

    //JOBS
    Route::resource('jobs', JobController::class, [
        'names' => [
            'index' => 'jobs.index',
            'create' => 'jobs.create',
            'store' => 'jobs.store',
            'show' => 'jobs.show',
            'edit' => 'jobs.edit',
            'update' => 'jobs.update',
            'destroy' => 'jobs.destroy',
        ],
    ]);
    Route::put('/jobs/{job}/update-start', [JobController::class, 'update_time'])->name('jobs.update_time');
    Route::put('/jobs/{job}/update-end', [JobController::class, 'update_end'])->name('jobs.update_end');
    Route::put('/jobs/{job}/update-status', [JobController::class, 'update_status'])->name('jobs.update_status');
    Route::put('/jobs/{job}/update-line', [JobController::class, 'add_invoice_line'])->name('jobs.add_product');
    Route::delete('/jobs/{job}/remove-line', [JobController::class, 'remove_invoice_line'])->name('jobs.remove_product');
    Route::get('/jobs/{job}/invoice-lines', [JobController::class, 'invoice_lines'])->name('jobs.invoice_lines');
    Route::post('/jobs/{job}/upload', [JobController::class, 'upload'])->name('jobs.upload');
    Route::post('/jobs/{job}/remove-file', [JobController::class, 'remove_file'])->name('jobs.remove_file');

    Route::post('/jobs/{job}/sum', [JobController::class, 'sum_invoiceable_hours'])->name('job.sum');
    Route::post('/jobs/{job}/add-plates-in-invoice', [JobController::class, 'addDrivingPlatesToInvoice'])->name('job.add-plates-in-invoice');

    Route::post('/jobs/updataCustomField', [JobController::class, 'updateCustomFields'])->name('job.updateCustomFields');

    Route::post('/jobs/updataCheckList', [JobController::class, 'updateCheckList'])->name('job.updataCheckLists');

    //ATTENDANCES

    Route::post('/attendances/{attendance}/updateNew', [AttendanceController::class, 'updateNew'])->name('attendances.updateNew')->middleware('permission:oprette tjek-ind-ud');
    Route::get('/attendances/getUserAttendances/{user}', [AttendanceController::class, 'getUserAttendances'])->name('attendances.getUserAttendances')->middleware('permission:oprette tjek-ind-ud');

    Route::get('/attendances/downloadexportSamletTimer', [AttendanceController::class, 'download'])->name('attendances.downloadexportSamletTimer');
    Route::get('/attendances/downloadexportIndberettedeTimer', [AttendanceController::class, 'exportIndberettedeTimer'])->name('attendances.downloadexportIndberettedeTimer');
    Route::resource('attendances', AttendanceController::class, [
        'names' => [
            'index' => 'attendances.index',
            'create' => 'attendances.create',
            'store' => 'attendances.store',
            'show' => 'attendances.show',
            'edit' => 'attendances.edit',
            'update' => 'attendances.update',
            'destroy' => 'attendances.destroy',
        ],
    ]);

    //Global search
    Route::post('/search', [GlobalSearchController::class, 'searchData'])->name('search');

    //INVOICES
    Route::post('/invoice/create', [InvoiceController::class, 'createInvoice'])->name('invoices.create');
    Route::get('/invoice/{id}', [InvoiceController::class, 'get_invoice'])->name('invoices.show');

    Route::middleware('auth:sanctum')->post('/markAsReadNotification', [NotificationController::class, 'markAsReadNotification'])->name('markAsReadNotification');
    Route::post('/markAllAsReadNotification', [NotificationController::class, 'markAllAsReadNotification'])->name('markAllAsReadNotification');
    //COLLECT
    Route::post('/collect/create', [InvoiceController::class, 'createCollectInvoice'])->name('invoices.create.collect');

    //JOURNAL ENTRIES
    Route::get('/journal-entries', [EconomicController::class, 'add_journal_entry'])->name('journal-entries.add');
    Route::post('/journal-entries', [EconomicController::class, 'add_journal_entry'])->name('journal-entries.add');
    Route::get('/collect', [InvoiceController::class, 'createCollectInvoice'])->name('collect');

    Route::resource('hours', HourController::class, [
        'names' => [
            'index' => 'hours.index',
            'create' => 'hours.create',
            'store' => 'hours.store',
            'show' => 'hours.show',
            'edit' => 'hours.edit',
            'update' => 'hours.update',
            'destroy' => 'hours.destroy',
        ],
    ])->only(['store', 'update', 'destroy']);

    Route::get('/tjekIndNow', [TjekIndController::class, 'index'])->name('tjekIndNow.index');
    Route::post('/tjekIndNow', [TjekIndController::class, 'store'])->name('tjekIndNow.store')->middleware(['idempotency']);
    Route::post('/tjekIndNow/updatapasuer', [TjekIndController::class, 'updataPasuer'])->name('tjekIndNow.updatapasuer');

    //GEOFENCES
    Route::post('/geofences/SetlocationOnThisPc', [GeofenceController::class, 'SetlocationOnThisPc'])->name('geofences.SetlocationOnThisPc');

    Route::resource('geofences', GeofenceController::class);

    Route::get('/economic/getallproductsobject/{economicAgreement}', [EconomicController::class, 'getAllproductsObject'])->name('economic.getallproductsobject');

    Route::post('customerlocal/storeEconomicCustomerreturn', [EconomicController::class, 'storeEconomicCustomerReturnObject'])->name('customerlocal.storeEconomicCustomerreturn');

});

require __DIR__.'/auth.php';

//API (move to API.php when auth is sorted)
Route::middleware('auth')->group(function () {

    Route::get('/economic/customers', [EconomicController::class, 'get_all_customers'])->name('economic.customers');
    Route::get('/economic/getallproductsobject/{economicAgreement}', [EconomicController::class, 'getAllproductsObject'])->name('economic.getallproductsobject');

    Route::get('/economic/layouts', [InvoiceLayoutController::class, 'all'])->name('economic.layouts');

});

//Refactored Routes:

Route::middleware('auth')->group(function () {

    ////////////////////////////////////////////
    //Resources
    ////////////////////////////////////////////

    //Economic Agreements
    Route::resource('economicagreement', EconomicAgreementController::class)->except(['show']);

    //Categories
    Route::resource('categories', CategoryController::class)->except(['show']);

    //Tariffs
    Route::resource('tariffs', TariffController::class)->except(['show']);

    //Settings
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');

    //Vacation
    Route::resource('vacations', VacationController::class)->except(['show', 'create', 'edit']);

    //Geofences
    Route::resource('geofences', GeofenceController::class)->except(['show']);

    //Users
    Route::resource('users', UserController::class)->except(['destroy']);
    Route::get('/users/{user}/forgot-password', [UserController::class, 'forgotPassword'])->name('users.forgotPassword');
    Route::post('/users/{user}/forgot-password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
    Route::get('/users/{user}/days', [UserController::class, 'days'])->name('users.days');
    Route::post('/users/{user}/days', [UserController::class, 'updateDays'])->name('users.updateDays');

    //Expenses
    Route::post('/expenses/sync/{job}', ExpenseController::class)->name('expenses.sync');

    //Customer
    route::resource('customers', CustomerController::class)->only(['index']);
    route::post('/customers/sync', [CustomerController::class, 'sync'])->name('customers.sync');

    //Products
    Route::resource('products', ProductController::class)->only(['index']);
    Route::post('/products/sync', [ProductController::class, 'sync'])->name('products.sync');

    Route::get('/job/{job}', function (Request $request, App\Models\Job $job) {
        return $job->load('customer');
    })->name('api.job.show');

    // Route::resource('worktimes', WorkTimeController::class)->only(['index', 'store']);
    // //get assigned jobs
    // Route::get('/worktimes/assigned/{user}', [WorkTimeController::class, 'allAssignedJobs'])->name('worktimes.assigned');

    route::post('customers/new', function (Request $request) {

        //validate request
        $request->validate([
            'name' => 'required',
            'economic_agreement_id' => 'required',
        ]);

        $customerservice = new CustomerService;
        $agreement = EconomicAgreement::find($request->economic_agreement_id);
        if (! $agreement) {
            return response()->json(['error' => 'Economic agreement not found'], 404);
        }

        $paymentTerms = $agreement->payment;

        $customerdata = [
            'name' => $request->name,
            'email' => $request->email ?? '',
            'address' => $request->address ?? '',
            'telephoneAndFaxNumber' => $request->phone ?? '',
            'city' => $request->city ?? '',
            'zip' => $request->zip ?? '',
            'vatZone' => [
                'vatZoneNumber' => 1,
            ],
            'currency' => 'DKK',
            'paymentTerms' => [
                'paymentTermsNumber' => $paymentTerms,

            ],
            'corporateIdentificationNumber' => $request->cvr ?? '',
            'customerGroup' => [
                'customerGroupNumber' => 1,
            ],

        ];

        $customer = $customerservice->createCustomer($customerdata, $agreement->economic_agreement);

        return redirect()->back()->with('newly_created', $customer);

    })->name('new.customer');

});

Route::middleware(['auth', 'email:webhusetballum.dk'])->group(function () {
    Route::get('/webhuset/features', [FeatureController::class, 'index'])->name('features.index');

    Route::post('/webhuset/features', [FeatureController::class, 'update'])->name('features.update');

    Route::post('/upload-icons', [FeatureController::class, 'uploadIcons'])->name('upload-icons');
    Route::post('/upload-logo', [FeatureController::class, 'uploadLogo'])->name('upload-logo');
});

Route::middleware(EnsureFeaturesAreActive::using('machines'))->group(function () {

    $prefix = 'machines';

    //for backwards compatibility
    if (! app()->runningInConsole()) {
        $prefix = app(WebhusetSettings::class)->machine_name_singular;

        $prefix = ! empty($prefix) ? Str::lower($prefix) : 'machines';

    }

    Route::resource($prefix, MachineController::class, [
        'parameters' => [
            $prefix => 'machine', // Force the parameter to always be named 'machine'
        ],
        'names' => [
            'index' => 'machines.index',
            'create' => 'machines.create',
            'store' => 'machines.store',
            'show' => 'machines.show',
            'edit' => 'machines.edit',
            'update' => 'machines.update',
            'destroy' => 'machines.destroy',

        ],
    ]);

});

 //WEATHER
 Route::get('/api/weather', [WeatherForecastController::class, 'getWeatherFromService'])->name('weather.get');

