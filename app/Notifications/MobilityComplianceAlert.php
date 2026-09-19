<?php

namespace App\Notifications;

use App\Models\DomainNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MobilityComplianceAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly DomainNotification $alert) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Aviso de cumplimiento de movilidad Erasmus+')
            ->line($this->alert->message)
            ->action('Ver movilidad', url('/admin/mobilities/'.$this->alert->mobility_id.'/edit'));
    }
}
