<template>
    <AppLayout title="Tester">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ connections.length }} {{ connections.length === 1 ? 'connection' : 'connections' }}
            </h2>
        </template>

        <div class="py-12 sm:px-6 lg:px-8 space-y-3">
            <div class="flex flex-col space-y-3">
                <div
                    v-for="(connection, i) in connections"
                    :key="i"
                    class="w-full"
                >
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-4 space-y-3">
                        <!-- CONNECTION -->
                        <div class="flex justify-between w-full">
                            <div class="truncate">
                                <div>
                                    {{ i + 1 }}.
                                    <span class="uppercase">{{ connection.status }}</span>
                                    -
                                    <span>
                                        {{ Object.keys(connection.channels).length > 0 ? Object.keys(connection.channels).join(', ') : 'No channels' }}
                                    </span>
                                </div>
                                <message-modal
                                    v-if="connection.signin"
                                    :message="JSON.stringify(JSON.parse(connection.authenticateAsUser), null, 4)"
                                >
                                    <span class="cursor-pointer hover:underline">
                                        Authenticated as: {{ connection.authenticateAsUser }}
                                    </span>
                                </message-modal>
                            </div>
                            <jet-danger-button @click="disconnect(i)">Close connection</jet-danger-button>
                        </div>
                        <!-- CONNECTION -->

                        <!-- <div>
                            {{ connection.messages }}
                        </div> -->

                        <!-- ADD CHANNEL -->
                        <div class="flex space-x-3">
                            <jet-input
                                v-model="connection.newChannelForm.name"
                                type="text"
                                placeholder="private-room.1"
                                @keyup.enter="subscribeToChannel(connection)"
                            />
                            <jet-select
                                v-if="isPresenceChannel(connection.newChannelForm.name)"
                                v-model="connection.newChannelForm.user_info"
                                :options="users.map(({ user_id, user_info }) => ({ label: `${user_info.name} (id: ${user_id})`, value: JSON.stringify(user_info) }))"
                            />
                            <jet-button @click="subscribeToChannel(connection)">Subscribe</jet-button>
                        </div>
                        <!-- ADD CHANNEL -->

                        <!-- CHANNELS -->
                        <div class="flex flex-col space-y-1">
                            <div
                                class="w-full bg-white overflow-hidden border border-2 sm:rounded-lg p-4 space-y-3"
                                v-for="({ messages }, channelName) in connection.channels"
                                :key="channelName"
                            >
                                <!-- CHANNEL TOP -->
                                <div>
                                    Channel: <span class="font-bold">{{ channelName }}</span>
                                    <button
                                        class="text-red-500 underline ml-2"
                                        @click="unsubscribeFromChannel(connection, channelName)"
                                    >
                                        Unsubscribe
                                    </button>
                                    <button
                                        class="underline ml-2"
                                        @click="resubscribeToChannel(connection, channelName)"
                                    >
                                        Resubscribe
                                    </button>
                                    <send-message-modal
                                        :connection="connection"
                                        :channel="channelName"
                                        :app="app"
                                        @on-client-message="onClientMessage"
                                    >
                                        <button class="underline ml-2">
                                            Send message
                                        </button>
                                    </send-message-modal>
                                </div>
                                <!-- CHANNEL TOP -->
                                <!-- CHANNEL MESSAGES -->
                                <div class="space-y-1">
                                    <message-modal
                                        v-for="(message, messageIndex) in messages"
                                        :key="messageIndex"
                                        :message="JSON.stringify(message, null, 4)"
                                    >
                                        <span class="truncate cursor-pointer hover:underline">
                                            {{ message }}
                                        </span>
                                    </message-modal>
                                </div>
                                <!-- CHANNEL MESSAGES -->
                            </div>
                        </div>
                        <!-- CHANNELS -->
                    </div>
                </div>
            </div>

            <div class="flex space-x-4 divide-x-4 divide-solid">
                <jet-button @click="newConnection()">New connection</jet-button>
                <div class="flex space-x-2 pl-4">
                    <jet-select
                        v-model="authenticateAsUser"
                        :options="users.map(({ user_id, user_info }) => ({ label: `${user_info.name} (id: ${user_id})`, value: JSON.stringify(user_info) }))"
                    />
                    <jet-button @click="newConnection(true)">New connection (signin)</jet-button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
import { defineComponent } from 'vue';
import AppLayout from '@/Layouts/AppLayout';
import JetButton from '@/Jetstream/Button';
import JetDangerButton from '@/Jetstream/DangerButton';
import JetInput from '@/Jetstream/Input';
import JetSelect from '@/Jetstream/Select';
import MessageModal from '@/Modals/MessageModal';
import Pusher from 'pusher-js';
import SendMessageModal from '@/Modals/SendMessageModal';

export default defineComponent({
    components: {
        AppLayout,
        JetButton,
        JetDangerButton,
        JetInput,
        JetSelect,
        MessageModal,
        SendMessageModal,
    },

    props: {
        users: {
            default: () => [],
        },
    },

    data() {
        return {
            app: {
                id: 'app-id',
                secret: 'app-secret',
                key: 'app-key',
                host: '127.0.0.1',
                port: 6001,
                tls: false,
            },
            connections: [],
            authorizePresenceChannelAsUser: null,
            authenticateAsUser: null,
        };
    },

    mounted() {
        // Enable this for development purposes.
        // this.newConnection();
    },

    computed: {
        connectedMessage() {
            return 'Not connected';
        },
    },

    methods: {
        newConnection(signin = false) {
            let authenticateAsUser = this.authenticateAsUser;

            let connection = new Pusher(this.app.key, {
                host: this.app.host,
                wssHost: this.app.host,
                wsHost: this.app.host,
                wsPort: this.app.port,
                wssPort: this.app.port,
                forceTLS: this.app.tls,
                encrypted: true,
                disableStats: true,
                enabledTransports: this.app.tls ? ['wss'] : ['ws'],
                authorizer: (channel, options) => {
                    return {
                        authorize: (socketId, callback) => {
                            axios.post('/authorize-channel', {
                                socket_id: socketId,
                                channel_name: channel.name,
                                user_info: this.authorizePresenceChannelAsUser,
                                app: JSON.stringify(this.app),
                            })
                            .then(response => {
                                callback(false, response.data);
                            })
                            .catch(error => {
                                callback(true, error);
                            });
                        }
                    };
                },
                userAuthentication: {
                    customHandler: ({ socketId }, callback) => {
                        axios.post('/authorize-connection', {
                            socket_id: socketId,
                            user_info: authenticateAsUser,
                            app: JSON.stringify(this.app),
                        })
                        .then(response => {
                            callback(false, response.data);
                        })
                        .catch(error => {
                            callback(true, error);
                        });
                    },
                },
            });

            let newLength = this.connections.push({
                pusher: connection,
                messages: [],
                statuses: [],
                status: 'pending',
                signin,
                authenticateAsUser,
                channels: {},
                newChannelForm: this.$inertia.form({
                    name: '',
                    user_info: '',
                }),
                lastPing: new Date(),
                signinSuccess: false,
            });

            let newConnIndex = newLength - 1;
            let newConn = this.connections[newConnIndex];

            if (signin) {
                connection.signin();
            }

            newConn.pusher.connection.bind('state_change', ({ previous, current }) => {
                newConn.statuses.push({ previous, current });
                newConn.status = current;
            });

            newConn.pusher.bind_global((event, data) => {
                if (event === 'pusher:pong') {
                    newConn.lastPing = new Date();
                }

                if (event === 'pusher:"signin_success') {
                    newConn.signinSuccess = true;
                }

                newConn.messages.push({ event, data });
            });
        },

        disconnect(index) {
            let connection = this.connections[index] ?? null;

            if (connection) {
                connection.pusher.disconnect();
                this.connections.splice(index, 1);
            }
        },

        subscribeToChannel(connection) {
            let channelName = connection.newChannelForm.name;

            if (! channelName) {
                return;
            }

            if (connection.channels[channelName] ?? false) {
                return;
            }

            this.authorizePresenceChannelAsUser = connection.newChannelForm.user_info;

            connection.channels[channelName] = {
                messages: [],
                user_info: this.authorizePresenceChannelAsUser,
            };

            connection.pusher.subscribe(channelName).bind_global((event, data) => {
                connection.channels[channelName].messages.push({ event, data });
            });

            connection.newChannelForm.reset();
        },

        unsubscribeFromChannel(connection, channelName) {
            connection.pusher.unsubscribe(channelName);
            delete connection.channels[channelName];
        },

        resubscribeToChannel(connection, channelName) {
            connection.newChannelForm.name = channelName;
            connection.newChannelForm.user_info = connection.channels[channelName].user_info;

            this.unsubscribeFromChannel(connection, channelName);
            this.subscribeToChannel(connection);
        },

        onClientMessage({ connection, channel, event, message }) {
            connection.pusher.channel(channel).trigger(`client-${event}`, JSON.parse(message));
        },

        isPresenceChannel(channelName) {
            return channelName.indexOf('presence-') === 0;
        },
    },
});
</script>
