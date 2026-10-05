<?php

namespace App\Notifications;

use App\Models\LoanApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public LoanApplication $loan;

    public string $message;

    public function __construct(
        LoanApplication $loan,
        string $message
    ) {
        $this->loan = $loan;
        $this->message = $message;
    }

    public function via($notifiable): array
    {
        return [
            'database',
            'mail',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)

            ->subject('Loan Application Update')

            ->greeting('Hello '.$notifiable->name)

            ->line($this->message)

            ->line('Loan Reference: '.$this->loan->reference_number)

            ->line('Current Status: '.ucwords(str_replace('_', ' ', $this->loan->status)))

            ->action(
                'View Loan',
                config('app.frontend_url').'/finance/loans/'.$this->loan->id
            )

            ->line('Thank you for using MkulimaHub.');
    }

    public function toArray($notifiable): array
    {
        return [

            'loan_id' => $this->loan->id,

            'reference_number' => $this->loan->reference_number,

            'status' => $this->loan->status,

            'message' => $this->message,

            'created_at' => now(),

        ];
    }
}