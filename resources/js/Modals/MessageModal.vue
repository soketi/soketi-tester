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
            Message Preview
        </template>

        <template #content>
            <form @submit.prevent="sendMessage">
                <div class="mt-4">
                    <v-ace-editor
                        v-model:value="message"
                        lang="json"
                        theme="chrome"
                        style="height: 500px"
                        :readonly="true"
                        :options="{ useWorker: true }"
                    />
                </div>
            </form>
        </template>

        <template #footer>
            <jet-secondary-button @click="closeModal">
                Cancel
            </jet-secondary-button>
        </template>
    </jet-dialog-modal>
</template>

<script>
import { defineComponent } from 'vue';
import JetDialogModal from '@/Jetstream/DialogModal';
import JetSecondaryButton from '@/Jetstream/SecondaryButton';
import { VAceEditor } from 'vue3-ace-editor';

import 'ace-builds/src-noconflict/mode-json';
import 'ace-builds/src-noconflict/theme-chrome';

export default defineComponent({
    components: {
        JetDialogModal,
        JetSecondaryButton,
        VAceEditor,
    },

    props: {
        classes: {
            default: ['block'],
        },
        message: {
            default: '{}',
        },
    },

    data() {
        return {
            showModal: false,
        };
    },

    methods: {
        closeModal() {
            this.showModal = false;
        },
    },
});
</script>
