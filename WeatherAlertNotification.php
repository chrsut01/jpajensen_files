<?php

namespace App\Notifications;

use App\Models\ImageSetting;
use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\VonageMessage;
use Illuminate\Notifications\Notification;

class WeatherAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $job;

    protected $alertForecast;

    public function __construct(Job $job, array $alertForecast)
    {
        $this->job = $job;
        $this->alertForecast = $alertForecast;
    }

    // To store the data in the notification table
    public function toArray($notifiable): array
    {
        return [
            'Title' => 'Vejr Advarsel',
            'job_title' => $this->job->title,
            'alert_date' => date('Y-m-d', strtotime($this->alertForecast['date'])),
            'conditions' => $this->alertForecast['condition'] ?? $this->alertForecast['description'],
            'alert_reason' => $this->alertForecast['alert_reasons'], // Single reason passed from service
            'job_id' => $this->job->id,

        ];
    }

    /**
     * Get the notification channels.
     */
    public function via($notifiable): array
    {

        return ['mail', 'database'];

        // // Return array of channels based on user preferences
        // $channels = ['mail']; // Email by default

        // // Add SMS if user has phone number and wants SMS notifications
        // if ($notifiable->phone_number && $notifiable->sms_notifications_enabled) {
        //     $channels[] = 'vonage'; // Laravel's default SMS provider
        // }

        // return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $date = date('Y-m-d', strtotime($this->alertForecast['date']));

        // Create temperature string based on available data
        $tempString = '';
        if (isset($this->alertForecast['min_temp'])) {
            $tempString .= 'Min: '.number_format($this->alertForecast['min_temp'], 1).'°C';
        }
        if (isset($this->alertForecast['max_temp'])) {
            $tempString .= isset($this->alertForecast['min_temp']) ? ', ' : '';
            $tempString .= 'Max: '.number_format($this->alertForecast['max_temp'], 1).'°C';
        }

        // Assuming company details are available through the notifiable or job relation
        $company = $notifiable->company ?? $this->job->company;

        return (new MailMessage)
            ->subject('Vejralarm for Job: '.$this->job->title)
            ->view('emails.weather-alert-custom', [
                'job' => $this->job,
                'date' => $date,
                'alertForecast' => $this->alertForecast,
                'tempString' => $tempString,
                'userName' => $notifiable->name ?? 'Kunde',
                'companyLogo' => ImageSetting::where('name', 'logo')->first()?->getFirstMediaUrl('sitelogo') ?? '',
                'companyName' => $notifiable->company->name ?? config('app.name'),
                'alertReason' => $this->alertForecast['alert_reasons'],
            ]);
    }

    /**
     * Get the Vonage / SMS representation of the notification.
     */
    public function toVonage($notifiable): VonageMessage
    {
        $date = date('Y-m-d', strtotime($this->alertForecast['date']));

        return (new VonageMessage)
            ->content("Vejralarm for Job: {$this->job->title} for d. {$date}: {$this->alertForecast['alert_reason']}.");
    }
}
