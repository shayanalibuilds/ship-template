<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Team;
use App\Models\TeamInvitation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

final class TeamInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly TeamInvitation $invitation)
    {
        //
    }

    /**
     * The delivery channels for the invitation.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $team = $this->invitation->team;

        $teamName = $team instanceof Team ? $team->name : 'your team';

        return (new MailMessage)
            ->subject('You are invited to join '.$teamName)
            ->greeting('Hello!')
            ->line('You have been invited to join the team "'.$teamName.'" on '.config('app.name').'.')
            ->action('Accept the invitation', $this->acceptUrl())
            ->line('This invitation was sent to '.$this->invitation->email.'. If you were not expecting it, you can ignore this email.');
    }

    /**
     * The signed accept URL from the email body.
     */
    private function acceptUrl(): string
    {
        return (string) URL::signedRoute(
            'teams.invitations.accept',
            ['invitation' => (int) $this->invitation->getKey()],
        );
    }
}
