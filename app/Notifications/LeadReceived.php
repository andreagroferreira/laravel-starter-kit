<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class LeadReceived extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public FormSubmission $submission) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $summary = collect($this->submission->data)
            ->map(fn (mixed $value, string $key): string => sprintf('%s: %s', $key, is_scalar($value) ? (string) $value : json_encode($value)))
            ->implode("\n");

        return (new MailMessage)
            ->subject(sprintf('Novo lead — %s', $this->submission->form->name))
            ->line(sprintf('O formulário «%s» do site «%s» recebeu uma nova submissão:', $this->submission->form->name, $this->submission->site->name))
            ->line($summary)
            ->action('Ver leads', url('/leads'));
    }
}
