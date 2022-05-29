<?php

namespace App\Http\Controllers;

use App\Events\Message;
use App\Models\FakeUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Config;

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
        // Set the Pusher credentials for the requested app.
        $app = json_decode($request->app, true);

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

        // Make sure that for presence channels, the request's user() method returns the fake one.
        if ($request->user_info) {
            $user = json_decode($request->user_info, true);

            Auth::setUser(new FakeUser([
                'id' => $user['id'],
                'name' => $user['name'],
            ]));
        }

        // Register a global channel for the Pusher driver, as we trust everything from the user.
        Broadcast::driver('pusher')->channel('{any}', function () use ($request) {
            return $request->user_info
                ? json_decode($request->user_info, true)
                : true;
        });

        // Auth with Pusher since we got the user and the connection right.
        return Broadcast::driver('pusher')->auth($request);
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
            'channel' => ['required', 'string'],
            'event' => ['required', 'string'],
            'message' => ['required', 'string', 'json'],
        ]);

        $message = new Message(
            json_decode($request->app, true),
            $request->channel,
            $request->event,
            json_decode($request->message, true),
        );

        if ($request->socket_id) {
            broadcast($message)->toOthers();
        } else {
            broadcast($message);
        }

        return ['ok' => true];
    }
}
