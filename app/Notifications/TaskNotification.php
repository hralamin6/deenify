<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TaskNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected ?string $url;
    protected string $icon;
    protected string $type;
    protected ?int $taskId;

    /**
     * Create a new notification instance.
     *
     * @param string $title The notification title
     * @param string $message The notification message
     * @param string|null $url The URL to navigate to when clicked
     * @param int|null $taskId The task ID (optional)
     * @param string $icon The icon name (default: o-check-circle)
     * @param string $type The notification type (success, error, warning, info)
     */
    public function __construct(
        string $title,
        string $message,
        ?string $url = null,
        ?int $taskId = null,
        string $icon = 'o-check-circle',
        string $type = 'info'
    ) {
        $this->title = $title;
        $this->message = $message;
        $this->url = $url;
        $this->taskId = $taskId;
        $this->icon = $icon;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return [WebPushChannel::class, 'database'];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        $webPush = (new WebPushMessage)
            ->title($this->title)
            ->body($this->message)
            ->icon(asset('logo.png'))
            ->badge(asset('logo.png'));

        if ($this->url) {
            $webPush->data([
                'url' => $this->url,
                'task_id' => $this->taskId,
            ])->action('View Task', 'view-task');
        }

        return $webPush;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'task_id' => $this->taskId,
            'icon' => $this->icon,
            'type' => $this->type,
        ];
    }
}
