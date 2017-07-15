<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegisteredUser extends Notification
{
  use Queueable;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct() {
    //
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed $notifiable
   *
   * @return array
   */
  public function via($notifiable) {
    return ['mail'];
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param  mixed $notifiable
   *
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail($notifiable) {
    return (new MailMessage)
      ->subject('Activate your account')
      ->line('Your account has been correctly created but you need to confirm it to use the application.')
      ->action('Confirm my account', url("/confirm/{$notifiable->id}/{$notifiable->confirmation_token}"))
      ->line('Thank you for using our application!');
  }
}
