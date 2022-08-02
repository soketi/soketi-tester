<script setup>
import { onMounted, ref } from 'vue';
import { VAceEditor } from 'vue3-ace-editor';
import JetDialogModal from '@/Jetstream/DialogModal.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';

import 'ace-builds/src-noconflict/mode-json';
import 'ace-builds/src-noconflict/theme-chrome';

const props = defineProps({
    classes: {
        default: ['block'],
    },
    message: {
        default: '{}',
    },
});

let showModal = ref(false);
let _message = ref('');

onMounted(() => {
    _message.value = props.message;
});
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
            Message Preview
        </template>

        <template #content>
            <form @submit.prevent="sendMessage">
                <div class="mt-4">
                    <v-ace-editor
                        v-model:value="_message"
                        lang="json"
                        theme="chrome"
                        style="height: 300px"
                        readonly
                        :options="{ useWorker: true }"
                    />
                </div>
            </form>
        </template>

        <template #footer>
            <JetSecondaryButton @click="showModal = false">
                Cancel
            </JetSecondaryButton>
        </template>
    </JetDialogModal>
</template>
