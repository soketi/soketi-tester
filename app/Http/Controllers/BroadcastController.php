<?php

namespace App\Http\Controllers;

use App\Models\FakeUser;
use Illuminate\Http\Request;
use Pusher\Pusher;

class BroadcastController extends Controller
{
    /**
     * Authorize the request for private and presence channels.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authorizeChannel(Request $request)
    {
        $request->validate([
            'app' => ['required', 'json'],
        ]);

        $app = json_decode($request->app, true);

        // Make sure that for presence channels, the request's user() method returns the fake one.
        if ($request->user_info) {
            $user = new FakeUser(json_decode($request->user_info, true));

            return $this->pusher($app)->presenceAuth(
                $request->channel_name,
                $request->socket_id,
                $user->getAuthIdentifier(),
                $user->toJson(),
            );
        }

        return $this->pusher($app)->socketAuth(
            $request->channel_name,
            $request->socket_id,
        );
    }

    /**
     * Broadcast a message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function broadcast(Request $request)
    {
        $request->validate([
            'app' => ['required', 'json'],
            'channel' => ['required', 'string'],
            'event' => ['required', 'string'],
            'message' => ['required', 'string', 'json'],
        ]);

        $app = json_decode($request->app, true);

        $this->pusher($app)->trigger(
            channels: [$request->channel],
            event: $request->event,
            data: json_decode($request->message, true),
            params: $request->socket_id ? ['socket_id' => $request->socket_id] : [],
        );

        return ['ok' => true];
    }

    /**
     * The Pusher instance for the app.
     *
     * @param  array  $app
     * @return Pusher
     */
    protected function pusher(array $app)
    {
        return new Pusher(
            $app['key'],
            $app['secret'],
            $app['id'],
            [
                'host' => $app['host'],
                'port' => $app['port'],
                'scheme' => $app['tls'] ? 'https' : 'http',
                'encrypted' => true,
                'useTLS' => $app['tls'],
            ],
        );
    }
}
