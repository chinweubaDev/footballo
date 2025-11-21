<?php

namespace App\Notifications;

use App\Models\Prediction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PredictionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $prediction;

    /**
     * Create a new notification instance.
     */
    public function __construct(Prediction $prediction)
    {
        $this->prediction = $prediction;
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
        $fixture = $this->prediction->fixture;
        
        return (new MailMessage)
            ->subject('New Football Prediction Available!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('A new prediction is now available for your review.')
            ->line('**Match:** ' . $fixture->home_team . ' vs ' . $fixture->away_team)
            ->line('**League:** ' . $fixture->league_name)
            ->line('**Date:** ' . $fixture->match_date->format('M d, Y H:i'))
            ->line('**Prediction:** ' . $this->prediction->tip)
            ->line('**Category:** ' . $this->prediction->category)
            ->line('**Confidence:** ' . $this->prediction->confidence . '%')
            ->when($this->prediction->odds, function ($message) {
                return $message->line('**Odds:** ' . $this->prediction->odds);
            })
            ->when($this->prediction->analysis, function ($message) {
                return $message->line('**Analysis:** ' . $this->prediction->analysis);
            })
            ->action('View Prediction', url('/predictions'))
            ->line('Thank you for using our football prediction service!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'prediction_id' => $this->prediction->id,
            'fixture_id' => $this->prediction->fixture_id,
            'match' => $this->prediction->fixture->home_team . ' vs ' . $this->prediction->fixture->away_team,
            'prediction' => $this->prediction->tip,
            'confidence' => $this->prediction->confidence,
        ];
    }
}
