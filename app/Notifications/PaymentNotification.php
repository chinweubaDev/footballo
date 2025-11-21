<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($this->payment->status === 'completed') {
            return $this->paymentSuccessMail($notifiable);
        } elseif ($this->payment->status === 'failed') {
            return $this->paymentFailedMail($notifiable);
        }

        return $this->paymentPendingMail($notifiable);
    }

    private function paymentSuccessMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Successful - Premium Access Activated!')
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('Your payment has been processed successfully and your premium subscription is now active.')
            ->line('**Plan:** ' . ucfirst(str_replace('_', ' ', $this->payment->plan_type)))
            ->line('**Amount:** ' . $this->payment->currency . ' ' . number_format($this->payment->amount))
            ->line('**Expires:** ' . $this->payment->expires_at->format('M d, Y'))
            ->line('You now have access to:')
            ->line('• Premium predictions')
            ->line('• Expert analysis')
            ->line('• High confidence tips')
            ->line('• Email notifications')
            ->action('Access Premium Content', url('/premium-tips'))
            ->line('Thank you for subscribing to our premium service!');
    }

    private function paymentFailedMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Failed - Please Try Again')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Unfortunately, your payment could not be processed.')
            ->line('**Plan:** ' . ucfirst(str_replace('_', ' ', $this->payment->plan_type)))
            ->line('**Amount:** ' . $this->payment->currency . ' ' . number_format($this->payment->amount))
            ->line('Please try again or contact our support team if the problem persists.')
            ->action('Try Again', url('/pricing'))
            ->line('If you continue to experience issues, please contact our support team.');
    }

    private function paymentPendingMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment Pending - Please Complete')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your payment is currently pending.')
            ->line('**Plan:** ' . ucfirst(str_replace('_', ' ', $this->payment->plan_type)))
            ->line('**Amount:** ' . $this->payment->currency . ' ' . number_format($this->payment->amount))
            ->line('Please complete your payment to activate your premium subscription.')
            ->action('Complete Payment', url('/pricing'))
            ->line('If you have already completed the payment, please wait a few minutes for it to be processed.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'status' => $this->payment->status,
            'amount' => $this->payment->amount,
            'currency' => $this->payment->currency,
            'plan_type' => $this->payment->plan_type,
        ];
    }
}
