<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
// use Illuminate\Broadcasting\PresenceChannel;
// use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use app\Models\Barometer;

class WeatherUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $cityBarometer;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Barometer $barometer)
    {
        $this->cityBarometer = $barometer;
        // dump($this->cityBarometer['city']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('public.weather.update');
    }
    public function broadcastWith()
    {
        return [
            'updatedCity' => $this->cityBarometer,
        ];
    }
}
