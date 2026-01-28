<?php

namespace App\Notifications;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class RecurringDonationPendingNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Donation $donation,
        public bool $isReminder = false
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        $preference = $notifiable->getNotificationPreference('recurring');

//        if ($preference->database_enabled) {
            $channels[] = 'database';
//        }

//        if ($preference->email_enabled) {
            $channels[] = 'mail';
//        }

//        if ($preference->push_enabled) {
            $channels[] = WebPushChannel::class;
//        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $campaignTitle = $this->donation->campaign?->title ?? __('your campaign');
        $subject = $this->isReminder
            ? __('Reminder: Recurring donation pending')
            : __('Recurring donation created');

        return (new MailMessage)
            ->subject($subject)
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('A recurring donation for ":campaign" is pending.', ['campaign' => $campaignTitle]))
            ->line(__('Amount: :amount', ['amount' => '৳'.number_format($this->donation->amount, 0)]))
            ->action(__('Pay Now'), route('web.campaign', $this->donation->campaign?->slug))
            ->line(__('Thank you for your support.'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->isReminder ? __('Recurring donation reminder') : __('Recurring donation pending'),
            'message' => __('You have a pending recurring donation for :campaign.', [
                'campaign' => $this->donation->campaign?->title ?? __('a campaign'),
            ]),
            'action_url' => route('web.campaign', $this->donation->campaign?->slug),
            'action_text' => __('Pay Now'),
            'icon' => 'o-arrow-path',
            'type' => 'info',
            'category' => 'recurring',
            'donation_id' => $this->donation->id,
        ];
    }

    public function toWebPush(object $notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->isReminder ? __('Recurring donation reminder') : __('Recurring donation pending'))
            ->body(__('You have a pending recurring donation.'))
            ->icon(asset('logo.png'))
            ->badge(asset('logo.png'))
            ->data([
                'url' => route('web.campaign', $this->donation->campaign?->slug),
                'category' => 'recurring',
            ])
            ->tag('recurring-donation-'.$this->donation->id);
    }
}
