<?php

namespace App\Http\Controllers;

use App\Models\FakeUser;
use Exception;
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
     * Authorize the connection.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authorizeConnection(Request $request)
    {
        $request->validate([
            'app' => ['required', 'json'],
        ]);

        $app = json_decode($request->app, true);

        $decodedString = "{$request->socket_id}::user::{$request->user_info}";

        $auth = $app['key'].':'.hash_hmac(
            'sha256',
            $decodedString,
            $app['secret'],
        );

        return [
            'auth' => $auth,
            'user_data' => $request->user_info,
        ];
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
     * Proxy a HTTP request to the Pusher app.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function proxyHttpRequest(Request $request)
    {
        $request->validate([
            'app' => ['required', 'json'],
            'method' => ['required', 'string', 'in:GET,POST,PUT,PATCH,DELETE'],
            'path' => ['required', 'string'],
            'payload' => ['required_unless:method,GET'],
            'parameters' => ['nullable', 'array'],
            'parameters.*.name' => ['required', 'string'],
            'parameters.*.value' => ['required', 'string'],
        ]);

        $request->params = collect($request->params ?? [])->mapWithKeys(function ($param) {
            return [$param['name'] => $param['value']];
        })->toArray();

        $request->payload = is_string($request->payload)
            ? $request->payload
            : json_encode($request->payload);

        $arguments = $request->method === 'GET'
            ? [$request->path, $request->params, true]
            : [$request->path, $request->payload, $request->params];

        try {
            $response = $this->pusher(json_decode($request->app, true))->{strtolower($request->method)}(
                ...$arguments,
            );
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'exception' => (string) $e,
            ]);
        }

        /** @var array $response */
        return response()->json([
            'success' => true,
            'response' => $response,
        ]);
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
