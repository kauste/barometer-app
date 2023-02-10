<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
// use Illuminate\Broadcasting\PresenceChannel;
// use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class WeatherUpdateFail implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lastUpdate;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($lastUpdate)
    {
        // if(Carbon::parse($lastUpdate)->diffInMinutes(Carbon::now()->addSeconds(60)) >= 1)
        // dump($lastUpdate);
        if($lastUpdate !== null 
            && Carbon::parse($lastUpdate)->diffInMinutes(Carbon::now()) >= 15)
        {
            $this->lastUpdate = Carbon::parse($lastUpdate)->locale('lt')->tz('Europe/Vilnius')->format('Y-m-d H:i');
        }
        else 
        {
            $this->lastUpdate = null;
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('public.weather.update.fail');
    }

}
