<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Telegram\TelegramMessage;
use Illuminate\Notifications\Notification;

class TelegramGithubActionNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public array $message, public $chatId, public $view, public $url = null)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['telegram'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toTelegram($notifiable)
    {
        if ($this->url) {
            return TelegramMessage::create()
                ->options([
                    'message_thread_id' => $this->chatId,
                    'parse_mode' => 'HTML',
                ])
                ->view($this->view, [
                    'message' => $this->message,
                ])
                ->button('View action', $this->url);
        } else {
            return TelegramMessage::create()
                ->options([
                    'message_thread_id' => $this->chatId,
                    'parse_mode' => 'HTML',
                ])
                ->view($this->view, [
                    'message' => $this->message,
                ]);
        }
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
