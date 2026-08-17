<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroadcastAlertPushedEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $title,
        public string $message,
        public string $severity, // INFO | WARNING | EMERGENCY
        public ?string $expiresAt = null
    ) {}

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'alert.pushed';
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('store-broadcasts'),
        ];
    }
}
