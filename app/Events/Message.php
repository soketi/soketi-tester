<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

class Message implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  array  $app
     * @param  string  $channelName
     * @param  string  $eventName
     * @param  array  $message
     * @return void
     */
    public function __construct(
        public array $app,
        public string $channelName,
        public string $eventName,
        public array $message,
    ) {
        Config::set('broadcasting.connections.pusher', [
            'driver' => 'pusher',
            'key' => $app['key'],
            'secret' => $app['secret'],
            'app_id' => $app['id'],
            'options' => [
                'host' => $app['host'],
                'port' => $app['port'],
                'scheme' => $app['tls'] ? 'https' : 'http',
                'encrypted' => true,
                'useTLS' => $app['tls'],
            ],
        ]);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel($this->channelName);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return $this->eventName;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return $this->message;
    }
}
