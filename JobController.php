<?php

namespace App\Http\Controllers;

use App\Http\Requests\RemoveFileRequest;
use App\Http\Requests\StoreJobRequest;
use App\Http\Requests\UpdateEndTimeRequest;
use App\Http\Requests\UpdateInvoiceLineRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Http\Requests\UpdateStartTimeRequest;
use App\Http\Requests\UploadFileRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\InvoiceLineResource;
use App\Http\Resources\JobResource;
use App\Models\Category;
use App\Models\Customer;
use App\Models\EconomicAgreement;
use App\Models\InvoiceLine;
use App\Models\Job;
use App\Models\Machine;
use App\Models\Product;
use App\Models\User;
use App\Services\JobMarginService;
use App\Tables\JobExpensesTable;
use App\Tables\JobHoursTable;
use App\Tables\Jobs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Pennant\Feature;
use App\Services\WeatherAlertService;
use App\Services\WeatherConditionService;
use App\Services\WeatherForecastService;

// use Request;

class JobController extends Controller
{
    protected $weatherConditionService;
    protected $weatherAlertService;
    protected $weatherForecastService;

    public function __construct(
        WeatherConditionService $weatherConditionService,
        WeatherAlertService $weatherAlertService,
        WeatherForecastService $weatherForecastService)
    {

        $this->weatherConditionService = $weatherConditionService;
        $this->weatherAlertService = $weatherAlertService;
        $this->weatherForecastService = $weatherForecastService;

        $this->middleware(['permission:se job'])->only(['index']);
        $this->middleware(['permission:skabe job'])->only(['create', 'store']);
        $this->middleware(['permission:redigere job'])->only(['edit', 'update']);
        $this->middleware(['permission:slette job'])->only(['destroy']);
        $this->middleware(['permission:se jobdetaljer'])->only(['show']);

        //For documentation
        $this->middleware(['permission:kan tilføje jobdokumenter'])->only(['upload']);
        $this->middleware(['permission:kan slette jobdokumenter'])->only(['remove_file']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, $id = null): Response
    {
                // Get jobs with weather conditions relationship
                $jobs = Job::with('weatherConditions')->get();

                // Process weather alerts for each job
                $jobsWithWeatherData = $jobs->map(function ($job) {
                    // Get weather forecast for job's dates and location
                    $weatherData = $this->weatherForecastService->getForecasts(
                        $job->lat,
                        $job->lng,
                        $job->start_date,
                        $job->end_date
                    );
        
                    // Check for alerts based on job's weather conditions
                    $weatherDataWithAlerts = $this->weatherAlertService->checkForAlerts(
                        $weatherData,
                        $job->weatherConditions->pluck('id')->toArray(),
                        $job
                    );
        
                    // Add weather data to job
                    $job->weatherData = $weatherDataWithAlerts;
        
                    return $job;
                });

        return Inertia::render('Jobs/JobsIndex', [
            'jobs' => Jobs::make($jobsWithWeatherData),
            'show_create_job_button' => $request->user()->can('skabe job'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return Inertia::render('Jobs/JobsCreate', [
            'users' => User::whereNot('status', 0)->get(),
            'categories' => Category::all(),
            'economicAgreements' => EconomicAgreement::all(),
            'machines' => Feature::when('machines', fn () => Machine::select('id', 'name', 'customer_id')->get()),
            'defaultEconomicAgreement' => $job->economicAgreement ?? EconomicAgreement::whereIsPrimary(true)->first() ?? null,
            'customers' => CustomerResource::collection(Customer::all()),
            'TopDesiredMarginPercentages' => (new JobMarginService)->getTopDesiredMarginPercentages(10),
            'weatherConditions' => $this->weatherConditionService->getWeatherConditions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJobRequest $request)
    {

        $job = Job::create([
            'title' => $request->title,
            'internal_description' => $request->internal_description ?? '',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'address' => $request->address,
            'lat' => $request->coordinates['lat'] ?? null,
            'lng' => $request->coordinates['lng'] ?? null,
            'category_id' => $request->category_id,
            'customer_id' => $request->customer_id,
            'status' => 'Ny',
            'ref_name' => $request->ref_name,
            'ref_number' => $request->ref_number,
            'ref_phone' => $request->ref_phone,
            'deadline' => $request->deadline,
            'created_by' => Auth::user()->id,
            'economic_agreement_id' => $request->economicAgreements,
            'static_price' => $request->static_price ?? null,
            'project_number' => $request->project_number ?? null,
            'expense_sync' => $request->expense_sync ?? null,
            'machine_id' => $request->machine_id ?? null,
            'desired_margin_percentage' => $request->desired_margin_percentage ?? null,
        ]);

        $job->user()->attach($request->users);

        // Attach weather conditions
        if ($request->has('weather_condition_ids')) {
            $job->weatherConditions()->attach($request->weather_condition_ids);
        }

        return redirect()->route('jobs.show', $job->id)
            ->with(['message' => 'Opgaven blev oprettet', 'status' => 'Success']);

    }

    /**
     * Display the specified resource.
     */
    public function show(Job $job)
    {

        if (! $job->economicAgreement) {
            return redirect()->route('jobs.index')->with(['message' => 'Kan ikke find den e-conomic aftale til den her opgave', 'status' => 'Error']);
        }

        $job->load(['media', 'hours', 'hours.user', 'invoiceLines', 'invoiceLines.owner',  'category', 'entries', 'customer']);

        $economic_controller = app(EconomicController::class);
        $Products = collect($economic_controller->getAllproducts($job->economicAgreement));

        $invoiceLines = $job->invoiceLines;

        $canSeeProductsPrice = Auth::user()->can('kan se kostpriser');

        $economic_agreement = $job->economicAgreement;

        $employeeHourProduct = [];
        $machineHourProduct = [];

        $employeeHourProductLoc = [1];

        $Products->whereIn('productNumber', $employeeHourProductLoc)->each(function ($product) use (&$employeeHourProduct) {
            $obj = [
                'id' => $product->productNumber,
                'name' => $product->name,
            ];
            array_push($employeeHourProduct, $obj);
        });

        $machineHourProductLoc = [1];

        $Products->whereIn('productNumber', $machineHourProductLoc)->each(function ($product) use (&$machineHourProduct) {
            $obj = [
                'id' => $product->productNumber,
                'name' => $product->name,
            ];
            array_push($machineHourProduct, $obj);
        });

        // Get base weather data
        $weatherData = $this->weatherForecastService->getForecasts(
            $job->lat,
            $job->lng,
            $job->start_date,
            $job->end_date
        );

        // $conditionIds = $job->weather_conditions;
        $conditionIds = $job->weatherConditions->pluck('id')->toArray();

        $weatherDataWithAlerts = $this->weatherAlertService->checkForAlerts(
            $weatherData,
            $conditionIds ?? [],
            $job
        );

        if (count($employeeHourProductLoc) == 0) {

            return redirect()->route('jobs.index')->with(['message' => 'Der mangler at blive valgt Mandskabstimer i economic aftale', 'status' => 'Error']);
        }

        return Inertia::render('Jobs/JobView', [
            'job' => new JobResource($job),
            'hours' => $job->hours,
            'invoice_lines' => $invoiceLines,
            'customer' => $job->customer,
            'users' => User::whereNot('status', 0)->get(),
            'products' => $job->economicAgreement->products,

            'canSeeProductsPrice' => $canSeeProductsPrice,
            'manhour' => Product::whereIn('id', $job->economicAgreement->mandskabstimer ?? [])->get(),
            'machinehour' => Product::whereIn('id', $job->economicAgreement->maskintimer ?? [])->get(),
            'jobhours' => new JobHoursTable($job),
            'entries' => Feature::when('expenses', fn () => (new JobExpensesTable($job))->as('expenses')),
            'entries_sum' => Feature::when('expenses', fn () => $job->entries_sum),
            'machine' => Feature::when('machines', fn () => $job->machine),
            'items' => InvoiceLineResource::collection($invoiceLines),
            'userMostUsedProducts' => Auth::user()->mostUsedProducts(),
            'agreementMostUsedProducts' => $job->economicAgreement->mostUsedProducts(),
            'weatherData' => $weatherDataWithAlerts,
            'weatherConditions' => $this->weatherConditionService->getWeatherConditions(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Job $job)
    {
        $job->load(['cmr', 'invoiceLines', 'customer', 'weatherConditions']);

        return inertia::render('Jobs/JobsEdit', [
            'job' =>  [
                ...$job->toArray(),
                'weatherConditions' => $job->weatherConditions,
            ],
            'users' => User::whereNot('status', 0)->get(),
            'economicAgreements' => EconomicAgreement::all(),
            'defaultEconomicAgreement' => $job->economicAgreement ?? EconomicAgreement::whereIsPrimary(true)->first()->pluck('id') ?? null,
            'categories' => Category::all(),
            'machines' => Feature::when('machines', fn () => Machine::select('id', 'name', 'customer_id')->get()),
            'customers' => CustomerResource::collection(Customer::all()),
            'TopDesiredMarginPercentages' => (new JobMarginService)->getTopDesiredMarginPercentages(10),
            'weatherConditions' => $this->weatherConditionService->getWeatherConditions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJobRequest $request, Job $job)
    {

        // Handle coordinates if provided
        if ($request->coordinates) {
            $request->merge([
                'lat' => $request->coordinates['lat'],
                'lng' => $request->coordinates['lng'],
            ]);
        }
        if ($request->has('economicAgreements')) {
            $request->merge([
                'economic_agreement_id' => $request->economicAgreements,
            ]);
        }

        // Add the 'updated_by' field
        $request->merge(['updated_by' => Auth::user()->id]);

        // Validate and update the job
        $job->update($request->all());

        // Check if 'users' exists in the request
        if ($request->has('users')) {
            // Sync the provided users with the job
            $job->user()->sync($request->users);
        }

        if ($request->has('weather_condition_ids')) {
            $job->weatherConditions()->sync($request->weather_condition_ids);
        }

        if (is_array($request->invoice_lines)) {
            // Collect the invoice lines for easy manipulation
            $invoiceLines = collect($request->invoice_lines);

            // Loop through each line and update or create as needed
            $invoiceLines->each(function ($line) use ($job) {
                $line = (object) $line;

                // Determine if we're updating an existing line or creating a new one
                $data = [
                    'description' => $line->description ?? '',
                    'price' => $line->price,
                    'discount' => $line->discount,
                    'discount_type' => $line->discount_type,
                    'quantity' => $line->quantity,
                    'cost_price' => $line->cost_price,
                ];

                if (isset($line->id)) {
                    // Update existing line
                    InvoiceLine::where('id', $line->id)->update($data);
                } else {
                    // Create a new line
                    InvoiceLine::create(array_merge($data, [
                        'job_id' => $job->id,
                        'date_added' => $line->date_added,
                    ]));
                }
            });

            // Delete invoice lines that are not in the request
            $job->invoiceLines()->whereNotIn('id', $invoiceLines->pluck('id')->filter())->delete();
        }

        // Redirect to the job details page
        return redirect()->back()->with(['message' => 'Opgaven blev opdateret', 'status' => 'Success']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Job $job)
    {
        $job->deleted_by = Auth::user()->id;
        $job->save();

        $job->delete();

        return redirect()->route('jobs.index')
            ->with(['message' => 'Opgaven blev slettet', 'status' => 'Success']);
    }

    public function update_time(UpdateStartTimeRequest $request, Job $job)
    {

        $job->update([
            'start_date' => $request->start_date,
        ]);
    }

    public function update_end(UpdateEndTimeRequest $request, Job $job)
    {

        $job->update([
            'end_date' => $request->end_date,
        ]);
    }

    public function update_status(UpdateJobRequest $request, Job $job)
    {

        $job->update([
            'status' => $request->status,
        ]);

        return redirect()->back();
    }

    public function add_invoice_line(UpdateInvoiceLineRequest $request, Job $job)
    {
        if (! $request->price) {
            $request->merge([
                'price' => 0,
            ]);
        }

        $job->invoiceLines()->create($request->all());

        return redirect()->back();
    }

    public function remove_invoice_line(UpdateInvoiceLineRequest $request, Job $job)
    {

        $line = $job->invoiceLines()->where('id', $request->id)->first();

        $line->delete();

        return redirect()->back();
    }

    //get invoice lines for a job
    public function invoice_lines(Job $job)
    {
        $invoiceLines = $job->invoiceLines;

        return $invoiceLines;
    }

    public function upload(UploadFileRequest $request, Job $job)
    {

        foreach ($request->file('files') as $file) {
            if ($request->has('collection')) {
                $job->addMedia($file)->toMediaCollection($request->collection);

                continue;
            }

            $job->addMedia($file)->toMediaCollection('job_files');
        }

        return redirect()->back();
    }

    public function remove_file(RemoveFileRequest $request, Job $job)
    {
        $media = $job->media()->where('id', $request->image_id)->first();
        if ($media) {
            $media->delete();
        }

        return redirect()->back();
    }

    public function sum_invoiceable_hours(Request $request, Job $job)
    {

        $allProducts = app(EconomicController::class)->getAllproducts($job->economicAgreement);

        //loop through request->hourdata as key value pair
        foreach ($request->hourdata as $key => $value) {

            //Using Arr find the product with the Number that matches the key
            $product = \Arr::first($allProducts, function ($product) use ($key) {
                return $product->productNumber == $key;
            });

            //find "IsPrimary" => "True" in ProductGroup. There can be multiple productgroups in productgroup
            /* $productGroup = \Arr::first($product['ProductGroup'], function ($productGroup) {
                return $productGroup['IsPrimary'] === 'True';
            });*/
            if (! empty($product)) {
                $productGroup = $product->productGroup->productGroupNumber;

                if ($job->static_price) {

                    $invoiceLines = $job->invoiceLines->where('product_number', $product->productNumber)->where('is_static', true)->first();
                    if ($invoiceLines) {
                        $invoiceLines->update([
                            'total_use' => $value + $invoiceLines->total_use,
                        ]);

                        continue;
                    }
                }

                InvoiceLine::create([
                    'product_number' => $key,
                    'product_group' => $productGroup,
                    'description' => $product->name,
                    'price' => $product->salesPrice,
                    'discount' => 0,
                    'discount_type' => 'percentage', // 'percentage' or 'amount
                    'quantity' => $value,
                    'job_id' => $job->id,
                    'date_added' => now(),
                    'cost_price' => $product->costPrice,

                ]);
            }
        }

        //redirect job show page
        return redirect()->back();
    }
}
