<?php

namespace App\SuperAdmin\Notifications\Front;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class NewUserOTP extends Notification
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
            ->subject('JnanaERP Signup OTP')
            ->greeting('Dear '. $this->notficationData['name'])
            ->lines([new HtmlString('Use ' .'<b>'. $this->notficationData['otp'].'</b>'.' as your OTP to signup. Never share your OTP with any unauthorized person. OTP is confidential.'),' This Email was sent using jnanaerp.com']
            );
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
