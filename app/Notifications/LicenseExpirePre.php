<?php

namespace App\SuperAdmin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LicenseExpirePre extends Notification
{
    use Queueable;

    public $notficationData;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($notficationData)
    {
        $this->notficationData = $notficationData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Important: JnanaERP License Expiry Notice')
            ->line('We hope this email finds you well. We are writing to inform you that the license for your JnanaERP software is nearing its expiration date. It is important to take action promptly to ensure uninterrupted access to your ERP system and avoid any disruption to your business operations. Your company license expiring on ' . $this->notficationData['expiry_date'] . '. Please renew your plan.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
