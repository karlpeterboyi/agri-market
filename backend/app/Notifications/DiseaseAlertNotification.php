<?php

namespace App\Notifications;

use App\Models\DiseaseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiseaseAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public DiseaseReport $diseaseReport,
        public string $alertType = 'outbreak_warning' // Options: outbreak_warning, financed_farm_alert, general_alert
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $commodity = ucfirst($this->diseaseReport->commodity_name ?? $this->diseaseReport->commodity_type);
        $location = "{$this->diseaseReport->district}, {$this->diseaseReport->region}";

        return (new MailMessage)
            ->subject("Disease Alert: Infection Reported in {$location}")
            ->greeting("Hello {$notifiable->name},")
            ->line("A potential disease incident has been reported in your area/portfolio.")
            ->line("Commodity: {$commodity}")
            ->line("Location: {$location}")
            ->line("Symptoms: {$this->diseaseReport->symptoms}")
            ->action('View Disease Report Details', url("/disease-reports/{$this->diseaseReport->id}"))
            ->line('Please take necessary preventative measures or review advisory recommendations.');
    }

    /**
     * Get the array representation of the notification for database storage.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'disease_report_id' => $this->diseaseReport->id,
            'alert_type' => $this->alertType,
            'commodity_type' => $this->diseaseReport->commodity_type,
            'commodity_name' => $this->diseaseReport->commodity_name,
            'region' => $this->diseaseReport->region,
            'district' => $this->diseaseReport->district,
            'status' => $this->diseaseReport->status,
            'message' => "Disease report logged for {$this->diseaseReport->commodity_name} in {$this->diseaseReport->district}.",
        ];
    }
}
