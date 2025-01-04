<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 
 *
 * @property int $id
 * @property int|null $open_weather_id
 * @property string $alert_condition
 * @property string|null $icon
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Job> $jobs
 * @property-read int|null $jobs_count
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherCondition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherCondition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherCondition query()
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherCondition whereAlertCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherCondition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherCondition whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherCondition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherCondition whereOpenWeatherId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WeatherCondition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class WeatherCondition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'open_weather_id',
        'alert_condition',
        'icon',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'open_weather_id' => 'integer',
    ];

    public function jobs()
    {
        return $this->belongsToMany(Job::class, 'job_weather_condition');
    }
}
