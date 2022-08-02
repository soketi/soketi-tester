<script setup>
import { onMounted, ref } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { VAceEditor } from 'vue3-ace-editor';
import JetButton from '@/Jetstream/Button.vue';
import JetDialogModal from '@/Jetstream/DialogModal.vue';
import JetInput from '@/Jetstream/Input.vue';
import JetInputError from '@/Jetstream/InputError.vue';
import JetLabel from '@/Jetstream/Label.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import JetSelect from '@/Jetstream/Select.vue';

const props = defineProps({
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
});

let showModal = ref(false);
let response = ref('');
let responseHeaders = ref(null);
let responseStatus = ref(null);

let form = useForm({
    path: '/channels',
    method: 'GET',
    payload: '{}',
    app: JSON.stringify(props.app),
});

onMounted(() => {
    if (props.path) {
        form.path = props.path;
    }

    if (props.method) {
        form.method = props.method;
    }

    if (props.payload) {
        form.payload = typeof props.payload === 'string'
            ? props.payload
            : JSON.stringify(props.payload);
    }
});

const sendRequest = () => {
    response.value = null;
    responseHeaders.value = null;
    responseStatus.value = null;

    axios.post(route('proxy-http-request'), form.data()).then((res) => {
        responseHeaders.value = res.headers;
        responseStatus.value = res.status;
        response.value = JSON.stringify(res.data, null, 4);
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
            API Playground
        </template>

        <template #content>
            <form @submit.prevent="sendRequest">
                <div class="mt-4">
                    <JetLabel
                        for="path"
                        value="Relative app path"
                    />
                    <JetInput
                        id="path"
                        v-model="form.path"
                        type="text"
                        class="w-full mt-1 block"
                        placeholder="/chanenls"
                        @keyup.enter="sendRequest"
                    />
                    <JetInputError
                        :message="form.errors.path"
                        class="mt-2"
                    />
                </div>

                <div class="mt-4">
                    <JetLabel
                        for="method"
                        value="HTTP Method"
                    />
                    <JetSelect
                        id="method"
                        v-model="form.method"
                        class="w-full mt-1 block"
                        :options="['GET', 'POST'].map(method => ({ label: method, value: method }))"
                    />
                    <JetInputError
                        :message="form.errors.method"
                        class="mt-2"
                    />
                </div>

                <div
                    v-if="form.method !== 'GET'"
                    class="mt-4"
                >
                    <JetLabel
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
                    <JetInputError
                        :message="form.errors.payload"
                        class="mt-2"
                    />
                </div>
            </form>

            <div
                v-if="response"
                class="mt-4"
            >
                <JetLabel
                    for="response"
                    value="Response"
                />
                <v-ace-editor
                    id="response"
                    v-model:value="response"
                    readonly
                    lang="json"
                    theme="chrome"
                    style="height: 300px"
                />

                <div>
                    Status: {{ responseStatus }}
                </div>
            </div>
        </template>

        <template #footer>
            <JetSecondaryButton @click="showModal = false">
                Cancel
            </JetSecondaryButton>

            <JetButton
                class="ml-3"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                @click="sendRequest"
            >
                Send
            </JetButton>
        </template>
    </JetDialogModal>
</template>
