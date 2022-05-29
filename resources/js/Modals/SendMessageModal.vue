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
                    <jet-input
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
                    <jet-input
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

                <div class="mt-4">
                    <v-ace-editor
                        v-model:value="form.message"
                        lang="json"
                        theme="chrome"
                        style="height: 500px"
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
import JetDialogModal from '@/Jetstream/DialogModal';
import JetInput from '@/Jetstream/Input';
import JetInputError from '@/Jetstream/InputError';
import JetLabel from '@/Jetstream/Label';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import { VAceEditor } from 'vue3-ace-editor';

export default defineComponent({
    components: {
        JetButton,
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

    data() {
        return {
            showModal: false,
            form: this.$inertia.form({
                channel: '',
                event: '',
                message: '{}',
                app: JSON.stringify(this.app),
            }),
        };
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
            this.form.post(route('broadcast'), {
                onSuccess: () => {
                    this.closeModal();
                },
            });
        },
    },
});
</script>
