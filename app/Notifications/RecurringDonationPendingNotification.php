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
            ? 'রিমাইন্ডার: আপনার নিয়মিত দান পরিশোধের সময় হয়েছে'
            : 'আপনার প্রতিশ্রুতি - নিয়মিত দানের পেমেন্ট';

        return (new MailMessage)
            ->subject($subject)
            ->greeting('আসসালামু আলাইকুম ' . $notifiable->name . ',')
            ->line('আল্লাহ আপনার ভালো করুন। দান করা একটি মহৎ কাজ যা ইহকাল ও পরকালে শান্তির কারণ।')
            ->line('আপনার ওয়াদা অনুযায়ী "' . $campaignTitle . '" ক্যাম্পেইনে আপনার নিয়মিত দান (৳' . number_format($this->donation->amount, 0) . ') প্রদানের সময় হয়েছে।')
            ->line('এই দানটি সম্পন্ন করে কল্যাণের পথে আপনার যাত্রা অব্যাহত রাখুন।')
            ->action('পেমেন্ট সম্পন্ন করুন', route('web.campaign', $this->donation->campaign?->slug) . '#recurring')
            ->line('আল্লাহ আপনার রিজিক বাড়িয়ে দিন। আমিন।');
    }

    public function toArray(object $notifiable): array
    {
        $url = route('web.campaign', $this->donation->campaign?->slug) . '#recurring';

        return [
            'title' => $this->isReminder ? __('Recurring donation reminder') : __('Recurring donation pending'),
            'message' => __('You have a pending recurring donation for :campaign.', [
                'campaign' => $this->donation->campaign?->title ?? __('a campaign'),
            ]),
            'url' => $url,
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
