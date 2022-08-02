<script setup>
import { onMounted, ref } from 'vue';
import ApiPlaygroundModal from '@/Modals/ApiPlaygroundModal.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import JetButton from '@/Jetstream/Button.vue';
import JetDangerButton from '@/Jetstream/DangerButton.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetSelect from '@/Jetstream/Select.vue';
import MessageModal from '@/Modals/MessageModal.vue';
import Pusher from 'pusher-js';
import SendMessageModal from '@/Modals/SendMessageModal.vue';
import { useForm } from '@inertiajs/inertia-vue3';

defineProps({
    users: {
        default: () => [],
    },
});

let app = ref({
    id: 'app-id',
    secret: 'app-secret',
    key: 'app-key',
    host: '127.0.0.1',
    port: 6001,
    tls: false,
});

let connections = ref([]);
let authorizePresenceChannelAsUser = ref(null);
let authenticateAsUser = ref(null);

onMounted(() => {
    // Enable this for development purposes.
    // newConnection();
});

const newConnection = (signin = false) => {
    let connection = new Pusher(app.value.key, {
        host: app.value.host,
        wssHost: app.value.host,
        wsHost: app.value.host,
        wsPort: app.value.port,
        wssPort: app.value.port,
        forceTLS: app.value.tls,
        encrypted: true,
        disableStats: true,
        enabledTransports: app.value.tls ? ['wss'] : ['ws'],
        authorizer: (channel, options) => {
            return {
                authorize: (socketId, callback) => {
                    axios.post('/authorize-channel', {
                        socket_id: socketId,
                        channel_name: channel.name,
                        user_info: authorizePresenceChannelAsUser.value,
                        app: JSON.stringify(app.value),
                    }).then(response => {
                        callback(false, response.data);
                    }).catch(error => {
                        callback(true, error);
                    });
                }
            };
        },
        userAuthentication: {
            customHandler: ({ socketId }, callback) => {
                axios.post('/authorize-connection', {
                    socket_id: socketId,
                    user_info: authenticateAsUser.value,
                    app: JSON.stringify(app.value),
                }).then(response => {
                    callback(false, response.data);
                }).catch(error => {
                    callback(true, error);
                });
            },
        },
    });

    let newLength = connections.value.push({
        pusher: connection,
        messages: [],
        statuses: [],
        status: 'pending',
        signin,
        authenticateAsUser,
        channels: {},
        newChannelForm: useForm({
            name: '',
            user_info: '',
        }),
        lastPing: new Date(),
        signinSuccess: false,
    });

    let newConnIndex = newLength - 1;
    let newConn = connections.value[newConnIndex];

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
};

const disconnect = (index) => {
    let connection = connections.value[index] ?? null;

    if (connection) {
        connection.pusher.disconnect();
        connections.value.splice(index, 1);
    }
};

const subscribeToChannel = (connection) => {
    let channelName = connection.newChannelForm.name;

    if (! channelName) {
        return;
    }

    if (connection.channels[channelName] ?? false) {
        return;
    }

    authorizePresenceChannelAsUser = connection.newChannelForm.user_info;

    connection.channels[channelName] = {
        messages: [],
        user_info: authorizePresenceChannelAsUser,
    };

    connection.pusher.subscribe(channelName).bind_global((event, data) => {
        connection.channels[channelName].messages.push({ event, data });
    });

    connection.newChannelForm.reset();
};

const unsubscribeFromChannel = (connection, channelName) => {
    connection.pusher.unsubscribe(channelName);
    delete connection.channels[channelName];
};

const resubscribeToChannel = (connection, channelName) => {
    connection.newChannelForm.name = channelName;
    connection.newChannelForm.user_info = connection.channels[channelName].user_info;

    unsubscribeFromChannel(connection, channelName);
    subscribeToChannel(connection);
};

const onClientMessage = ({ connection, channel, event, message }) => {
    connection.pusher.channel(channel).trigger(`client-${event}`, JSON.parse(message));
};

const isPresenceChannel = (channelName) => {
    return channelName.indexOf('presence-') === 0;
};
</script>

<template>
    <AppLayout title="Tester">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ connections.length }} {{ connections.length === 1 ? 'connection' : 'connections' }}
            </h2>
            <div>
                <api-playground-modal
                    method="GET"
                    path="/channels"
                    :app="app"
                >
                    <button class="underline">
                        Open API Playground
                    </button>
                </api-playground-modal>
            </div>
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
                            <JetDangerButton @click="disconnect(i)">
                                Close connection
                            </JetDangerButton>
                        </div>
                        <!-- CONNECTION -->

                        <!-- <div>
                            {{ connection.messages }}
                        </div> -->

                        <!-- ADD CHANNEL -->
                        <div class="flex space-x-3">
                            <JetInput
                                v-model="connection.newChannelForm.name"
                                type="text"
                                placeholder="private-room.1"
                                @keyup.enter="subscribeToChannel(connection)"
                            />
                            <JetSelect
                                v-if="isPresenceChannel(connection.newChannelForm.name)"
                                v-model="connection.newChannelForm.user_info"
                                :options="users.map(({ user_id, user_info }) => ({ label: `${user_info.name} (id: ${user_id})`, value: JSON.stringify(user_info) }))"
                            />
                            <JetButton @click="subscribeToChannel(connection)">
                                Subscribe
                            </JetButton>
                        </div>
                        <!-- ADD CHANNEL -->

                        <!-- CHANNELS -->
                        <div class="flex flex-col space-y-1">
                            <div
                                v-for="({ messages }, channelName) in connection.channels"
                                :key="channelName"
                                class="w-full bg-white overflow-hidden border-2 sm:rounded-lg p-4 space-y-3"
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

            <!-- NEW CONNECTIONS -->
            <div class="flex space-x-4 divide-x-4 divide-solid">
                <JetButton @click="newConnection">
                    New connection
                </JetButton>
                <div class="flex space-x-2 pl-4">
                    <JetSelect
                        v-model="authenticateAsUser"
                        :options="users.map(({ user_id, user_info }) => ({ label: `${user_info.name} (id: ${user_id})`, value: JSON.stringify(user_info) }))"
                    />
                    <JetButton @click="newConnection(true)">
                        New connection (signin)
                    </JetButton>
                </div>
            </div>
            <!-- NEW CONNECTIONS -->
        </div>
    </AppLayout>
</template>
