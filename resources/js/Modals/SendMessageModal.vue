<script setup>
import { onMounted, ref, watch } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { VAceEditor } from 'vue3-ace-editor';
import JetButton from '@/Jetstream/Button.vue';
import JetCheckbox from '@/Jetstream/Checkbox.vue';
import JetDialogModal from '@/Jetstream/DialogModal.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';

const props = defineProps({
    classes: {
        default: ['inline-block'],
    },
    connection: {
        default: () => ({}),
    },
    channel: {
        default: null,
    },
    app: {
        default: () => ({
            id: 'app-id',
            secret: 'app-secret',
            key: 'app-key',
            host: '127.0.0.1',
            port: 6001,
            tls: false,
        }),
    },
});

const emit = defineEmits(['onClientMessage']);

let showModal = ref(false);
let sendAsClientMessage = ref(false);
let sendToOthers = ref(false);

let form = useForm({
    channel: '',
    event: '',
    message: '{}',
    app: JSON.stringify(props.app),
    socket_id: '',
});

watch(sendAsClientMessage, (newValue) => {
    if (newValue === true) {
        sendToOthers = false;
        form.socket_id = '';
    }
});

watch(sendToOthers, (newValue) => {
    if (newValue === true) {
        sendAsClientMessage.value = false;
        form.socket_id = props.connection.pusher.connection.socket_id;
    }
});

onMounted(() => {
    if (props.channel) {
        form.channel = props.channel;
    }
});

const sendMessage = () => {
    if (sendAsClientMessage.value) {
        emit('onClientMessage', {
            ...form.data(),
            connection: props.connection,
        });

        showModal.value = false;

        return;
    }

    axios.post(route('broadcast'), form.data()).then(() => {
        showModal.value = false;
    });
};
</script>

<template>
    <div
        :class="classes"
        @click="showModal = true"
    >
        <slot />
    </div>
    <JetDialogModal
        :show="showModal"
        @close="showModal = false"
    >
        <template #title>
            Send a message
        </template>

        <template #content>
            <form @submit.prevent="sendMessage">
                <div class="mt-4">
                    <JetLabel
                        for="channel"
                        value="Channel"
                    />
                    <JetInput
                        id="channel"
                        v-model="form.channel"
                        type="text"
                        class="w-full mt-1 block"
                        placeholder="private-room.1"
                        @keyup.enter="sendMessage"
                    />
                    <JetInputError
                        :message="form.errors.channel"
                        class="mt-2"
                    />
                </div>

                <div class="mt-4">
                    <JetLabel
                        for="event_name"
                        value="Event name"
                    />
                    <JetInput
                        id="event_name"
                        v-model="form.event"
                        type="text"
                        class="w-full mt-1 block"
                        placeholder="my:event"
                        @keyup.enter="sendMessage"
                    />
                    <JetInputError
                        :message="form.errors.event"
                        class="mt-2"
                    />
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <JetCheckbox v-model:checked="sendAsClientMessage" />
                        <span class="ml-2 text-sm text-gray-600">Send as client event</span>
                    </label>
                </div>

                <div
                    v-if="!sendAsClientMessage"
                    class="block mt-4"
                >
                    <label class="flex items-center">
                        <JetCheckbox v-model:checked="sendToOthers" />
                        <span class="ml-2 text-sm text-gray-600">Broadcast to others</span>
                    </label>
                </div>

                <div class="mt-4">
                    <JetLabel
                        for="message"
                        value="Message"
                    />
                    <v-ace-editor
                        id="message"
                        v-model:value="form.message"
                        lang="json"
                        theme="chrome"
                        style="height: 300px"
                    />
                    <JetInputError
                        :message="form.errors.message"
                        class="mt-2"
                    />
                </div>
            </form>
        </template>

        <template #footer>
            <JetSecondaryButton @click="showModal = false">
                Cancel
            </JetSecondaryButton>

            <JetButton
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="sendMessage"
            >
                Send
            </JetButton>
        </template>
    </JetDialogModal>
</template>
