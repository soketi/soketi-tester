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
            API Playground
        </template>

        <template #content>
            <form @submit.prevent="sendRequest">
                <div class="mt-4">
                    <jet-label
                        for="path"
                        value="Relative app path"
                    />
                    <jet-input
                        id="path"
                        v-model="form.path"
                        type="text"
                        class="w-full mt-1 block"
                        placeholder="/chanenls"
                        @keyup.enter="sendRequest"
                    />
                    <jet-input-error
                        :message="form.errors.path"
                        class="mt-2"
                    />
                </div>

                <div class="mt-4">
                    <jet-label
                        for="method"
                        value="HTTP Method"
                    />
                    <jet-select
                        id="method"
                        v-model="form.method"
                        class="w-full mt-1 block"
                        :options="['GET', 'POST'].map(method => ({ label: method, value: method }))"
                    />
                    <jet-input-error
                        :message="form.errors.method"
                        class="mt-2"
                    />
                </div>

                <div
                    v-if="form.method !== 'GET'"
                    class="mt-4"
                >
                    <jet-label
                        for="payload"
                        value="Payload"
                    />
                    <v-ace-editor
                        id="payload"
                        v-model:value="form.payload"
                        lang="json"
                        theme="chrome"
                        style="height: 300px"
                    />
                    <jet-input-error
                        :message="form.errors.payload"
                        class="mt-2"
                    />
                </div>
            </form>

            <div
                v-if="response"
                class="mt-4"
            >
                <jet-label
                    for="response"
                    value="Response"
                />
                <v-ace-editor
                    id="response"
                    v-model:value="response"
                    :readonly="true"
                    lang="json"
                    theme="chrome"
                    style="height: 300px"
                />
            </div>
        </template>

        <template #footer>
            <jet-secondary-button @click="closeModal">
                Cancel
            </jet-secondary-button>

            <jet-button
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="sendRequest"
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
import JetSelect from '@/Jetstream/Select';
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
        JetSelect,
        VAceEditor,
    },

    emits: [],

    props: {
        classes: {
            default: ['inline-block'],
        },
        path: {
            default: '/channels',
        },
        method: {
            default: 'GET',
        },
        payload: {
            default: '{}',
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
            response: '',
            form: this.$inertia.form({
                path: '/channels',
                method: 'GET',
                payload: '{}',
                app: JSON.stringify(this.app),
            }),
        };
    },

    mounted() {
        if (this.path) {
            this.form.path = this.path;
        }

        if (this.method) {
            this.form.method = this.method;
        }

        if (this.payload) {
            this.form.payload = typeof this.payload === 'string'
                ? this.payload
                : JSON.stringify(this.payload);
        }
    },

    methods: {
        closeModal() {
            this.showModal = false;
        },

        sendRequest() {
            this.response = null;

            axios.post(route('proxy-http-request'), this.form.data()).then((response) => {
                this.response = JSON.stringify(response.data, null, 4);
            });
        },
    },
});
</script>
