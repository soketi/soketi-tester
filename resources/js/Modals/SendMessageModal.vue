<template>
    <div
        :class="classes"
        @click="showModal = true"
    >
        <slot />
    </div>
    <jet-dialog-modal
        :show="showModal"
        @close="closeModal"
    >
        <template #title>
            Send a message
        </template>

        <template #content>
            <form @submit.prevent="sendMessage">
                <div class="mt-4">
                    <jet-label
                        for="channel"
                        value="Channel"
                    />
                    <jet-input
                        id="channel"
                        v-model="form.channel"
                        type="text"
                        class="w-full mt-1 block"
                        placeholder="private-room.1"
                        @keyup.enter="sendMessage"
                    />
                    <jet-input-error
                        :message="form.errors.channel"
                        class="mt-2"
                    />
                </div>

                <div class="mt-4">
                    <jet-label
                        for="event_name"
                        value="Event name"
                    />
                    <jet-input
                        id="event_name"
                        v-model="form.event"
                        type="text"
                        class="w-full mt-1 block"
                        placeholder="my:event"
                        @keyup.enter="sendMessage"
                    />
                    <jet-input-error
                        :message="form.errors.event"
                        class="mt-2"
                    />
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <jet-checkbox v-model:checked="sendAsClientMessage" />
                        <span class="ml-2 text-sm text-gray-600">Send as client event</span>
                    </label>
                </div>

                <div
                    v-if="!sendAsClientMessage"
                    class="block mt-4"
                >
                    <label class="flex items-center">
                        <jet-checkbox v-model:checked="sendToOthers" />
                        <span class="ml-2 text-sm text-gray-600">Broadcast to others</span>
                    </label>
                </div>

                <div class="mt-4">
                    <jet-label
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
                    <jet-input-error
                        :message="form.errors.message"
                        class="mt-2"
                    />
                </div>
            </form>
        </template>

        <template #footer>
            <jet-secondary-button @click="closeModal">
                Cancel
            </jet-secondary-button>

            <jet-button
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="sendMessage"
            >
                Send
            </jet-button>
        </template>
    </jet-dialog-modal>
</template>

<script>
import { defineComponent } from 'vue';
import JetButton from '@/Jetstream/Button';
import JetCheckbox from '@/Jetstream/Checkbox';
import JetDialogModal from '@/Jetstream/DialogModal';
import JetInput from '@/Jetstream/Input';
import JetInputError from '@/Jetstream/InputError';
import JetLabel from '@/Jetstream/Label';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import { VAceEditor } from 'vue3-ace-editor';

export default defineComponent({
    components: {
        JetButton,
        JetCheckbox,
        JetDialogModal,
        JetInput,
        JetInputError,
        JetLabel,
        JetSecondaryButton,
        VAceEditor,
    },

    props: {
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
    },

    emits: ['onClientMessage'],

    data() {
        return {
            showModal: false,
            sendAsClientMessage: false,
            sendToOthers: false,
            form: this.$inertia.form({
                channel: '',
                event: '',
                message: '{}',
                app: JSON.stringify(this.app),
                socket_id: '',
            }),
        };
    },

    watch: {
        sendAsClientMessage(newValue) {
            if (newValue === true) {
                this.sendToOthers = false;
                this.form.socket_id = '';
            }
        },

        sendToOthers(newValue) {
            if (newValue === true) {
                this.sendAsClientMessage = false;
                this.form.socket_id = this.connection.pusher.connection.socket_id;
            }
        },
    },

    mounted() {
        if (this.channel) {
            this.form.channel = this.channel;
        }
    },

    methods: {
        closeModal() {
            this.showModal = false;
        },

        sendMessage() {
            if (this.sendAsClientMessage) {
                this.$emit('onClientMessage', {
                    ...this.form.data(),
                    connection: this.connection,
                });

                this.closeModal();

                return;
            }

            axios.post(route('broadcast'), this.form.data()).then(() => {
                this.closeModal();
            });
        },
    },
});
</script>
