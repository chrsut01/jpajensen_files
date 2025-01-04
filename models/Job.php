<?php

namespace App\Models;

use App\Settings\GeneralSettings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;
use LasseRafn\Economic\Economic;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * App\Models\Job
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $internal_description
 * @property string|null $note_to_customer
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $address
 * @property float|null $lat
 * @property float|null $lng
 * @property int|null $parent_job
 * @property string|null $shipping
 * @property int|null $total_driving_plates
 * @property int $remaining_driving_plates
 * @property string $status
 * @property int|null $customer_id
 * @property string|null $ref_name
 * @property string|null $ref_number
 * @property string|null $ref_phone
 * @property string|null $invoice_sent
 * @property int $economic_agreement_id
 * @property string|null $static_price
 * @property int|null $category_id
 * @property string|null $project_number
 * @property bool $expense_sync
 * @property int|null $machine_id
 * @property string|null $desired_margin_percentage
 * @property-read \App\Models\Category|null $category
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $cmr
 * @property-read int|null $cmr_count
 * @property-read \App\Models\User|null $createdBy
 * @property-read \App\Models\Customer|null $customer
 * @property-read \App\Models\User|null $deletedBy
 * @property-read \App\Models\EconomicAgreement $economicAgreement
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Entry> $entries
 * @property-read int|null $entries_count
 * @property-read mixed $assigned_users
 * @property-read mixed $available_machine_hours
 * @property-read mixed $available_man_hours
 * @property-read mixed $data
 * @property-read mixed $entries_sum
 * @property-read mixed $entry
 * @property-read mixed $hour_machine_duration
 * @property-read mixed $hour_man_duration
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Hour> $hours
 * @property-read int|null $hours_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceLine> $invoiceLines
 * @property-read int|null $invoice_lines_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\InvoiceLine> $invoice_lines
 * @property-read \App\Models\Machine|null $machine
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\User|null $updatedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $user
 * @property-read int|null $user_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WeatherCondition> $weatherConditions
 * @property-read int|null $weather_conditions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Job active()
 * @method static \Database\Factories\JobFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Job newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Job newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Job onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Job query()
 * @method static \Illuminate\Database\Eloquent\Builder|Job started()
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereDesiredMarginPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereEconomicAgreementId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereExpenseSync($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereInternalDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereInvoiceSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereLng($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereMachineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereNoteToCustomer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereParentJob($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereProjectNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereRefName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereRefNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereRefPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereRemainingDrivingPlates($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereStaticPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereTotalDrivingPlates($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Job withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Job withoutTrashed()
 * @mixin \Eloquent
 */
class Job extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, Notifiable, Searchable, SoftDeletes;

    protected $fillable = [
        'title',
        'internal_description',
        'start_date',
        'end_date',
        'address',
        'lat',
        'lng',
        'category_id',
        'status',
        'customer_id',
        'note_to_customer',
        'ref_name',
        'ref_number',
        'ref_phone',
        'invoice_sent',
        'deadline',
        'created_by',
        'updated_by',
        'deleted_by',
        'economic_agreement_id',
        'shipping',
        'static_price',
        'project_number',
        'expense_sync',
        'machine_id',
        'desired_margin_percentage',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'deadline' => 'datetime',
        'lat' => 'float',
        'lng' => 'float',
        'expense_sync' => 'boolean',

    ];

    protected $with = [
        'user',
    ];

    protected $appends = [
        'assigned_users',
    ];

    public function getAssignedUsersAttribute()
    {
        $users = $this->user->toArray();
        $users = array_reduce($users, function ($element1, $usr) {
            return $element1.$usr['name'].' ';
        }, '');

        return trim($users);
    }

    //category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsToMany(User::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'job_user');
    }

    // /scope start_date is greater than now
    public function scopeActive($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeStarted($query)
    {
        return $query->where('status', 'Påbegyndt');
    }

    public function invoiceLines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function invoice_lines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function jobTotalExVat()
    {
        $total = 0;
        foreach ($this->invoiceLines as $invoiceLine) {
            $total += $invoiceLine->price * $invoiceLine->quantity;
        }

        return $total;
    }

    public function jobTotalCost()
    {
        $total = 0;
        foreach ($this->invoiceLines as $invoiceLine) {
            $total += $invoiceLine->cost_price * $invoiceLine->quantity;
        }

        return $total;
    }

    //hours
    public function hours()
    {
        return $this->hasMany(Hour::class);
    }

    //belongs to economic agreement
    public function economicAgreement()
    {
        return $this->belongsTo(EconomicAgreement::class);
    }

    //job cmr
    public function cmr()
    {
        return $this->hasMany(Media::class, 'model_id')->where('collection_name', 'like', 'cmr%');
    }

    //FILE COLLECTIONS

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cmr')
            ->singleFile();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }

    // For Laravel Scout
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'internal_description' => $this->internal_description,
            'address' => $this->address,
            'category' => $this->category,
        ];
    }

    public function getDataAttribute()
    {
        return $this;
    }

    public function getEntryAttribute()
    {
        return Entry::where('projectNumber', $this->id)->get();
    }

    public function entries()
    {
        return $this->when($this->project_number, function () {
            return $this->hasMany(Entry::class, 'projectNumber', 'project_number');
        }, function () {
            return $this->hasMany(Entry::class, 'job_id', 'id');
        });

    }

    public function getEntriesSumAttribute()
    {
        $accounts = app(GeneralSettings::class)->expenses_accounts;

        $entries = $this->entries()->whereIn('accountNumber', $accounts)->sum('entries.amountInBaseCurrency');

        $invoiceLines = $this->invoiceLines()->selectRaw(' (cost_price * quantity) as total_cost')->get()->sum('total_cost');

        return $entries + $invoiceLines;
    }

    //get job hour man duration using attribute from hour model
    public function getHourManDurationAttribute()
    {
        return $this->hours()
            ->where('hour_type', Hour::EMPLOYEE_HOURS)
            ->get()
            ->sum(function ($hour) {
                return $hour->duration; // Use the dynamic attribute here
            });
    }

    public function getHourMachineDurationAttribute()
    {
        return $this->hours()
            ->where('hour_type', Hour::MACHINE_HOURS)
            ->get()
            ->sum(function ($hour) {
                return $hour->duration; // Use the dynamic attribute here
            });
    }

    public function machine()
    {

        return $this->belongsTo(Machine::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    // Available Man Hours as an accessor
    public function getAvailableManHoursAttribute()
    {
        return Product::whereIn('id', $this->economicAgreement->mandskabstimer ?? [])->get();
    }

    // Available Machine Hours as an accessor
    public function getAvailableMachineHoursAttribute()
    {
        return Product::whereIn('id', $this->economicAgreement->maskintimer ?? [])->get();
    }

    public function weatherConditions()
    {
        return $this->belongsToMany(WeatherCondition::class, 'job_weather_condition');
    }
}
