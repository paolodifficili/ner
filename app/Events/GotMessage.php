<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\Log;


class GotMessage implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $message = 'HEY!!!';
    public string $status = 'HEY!!!';
    public string $action = 'HEY!!!';


    /**
     * Create a new event instance.
     */
    public function __construct($m)
    {
        $this->message = $m['message'];
        $this->status = $m['status'];
        $this->action = $m['action'];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        Log::debug('GotMessage:broadcastOn!', [] );
        return [
            new Channel('chat'),
            // new PrivateChannel('channel-name'),
        ];
    }
}
