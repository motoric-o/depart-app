<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BookingConfirmed extends Notification
{
    use Queueable;

    protected $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    // 1. Specify we want to use the Database channel
    public function via($notifiable)
    {
        return ['database']; 
    }

    // 2. Define the data structure saved in the DB
    public function toDatabase($notifiable)
    {
        return [
            'title' => 'Booking Confirmed!',
            'message' => 'Your trip to ' . $this->booking->schedule->route->destination->city_name . ' is confirmed.',
            'booking_id' => $this->booking->id,
            'type' => 'success' // helpful for frontend styling (color)
        ];
    }
}