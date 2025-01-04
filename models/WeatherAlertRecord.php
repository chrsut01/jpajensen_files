<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

/**
 * 
 *
 * @property int $id
 * @property int $user_id
 * @property int $job_id
 * @property \Illuminate\Support\Carbon $alert_date
 * @property string $alert_reason
 * @property string $condition
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Job $job
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherAlertRecord newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherAlertRecord newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherAlertRecord query()
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherAlertRecord whereAlertDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherAlertRecord whereAlertReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherAlertRecord whereCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherAlertRecord whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherAlertRecord whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherAlertRecord whereJobId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherAlertRecord whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherAlertRecord whereUserId($value)
 * @mixin \Eloquent
 */
class WeatherAlertRecord extends Model
{
    protected $table = 'weather_alert_records';

    protected $fillable = [
        'user_id',
        'job_id',
        'alert_date',
        'alert_reason',
        'condition',
    ];

    protected $casts = [
        'alert_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    // Prunable trait to delete old records
    public function prunable()
    {
        return static::where('alert_date', '<', now()->startOfDay());
    }
}
