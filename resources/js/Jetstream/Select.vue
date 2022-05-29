<template>
    <select
        :value="modelValue"
        class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
        @change="$emit('update:modelValue', $event.target.value)"
    >
        <option
            v-for="({ value, label, disabled = false }, i) in options"
            :key="i"
            :value="value"
            :disabled="disabled"
        >
            {{ label }}
        </option>
    </select>
</template>

<script>
import { defineComponent } from 'vue'

export default defineComponent({
    props: {
        modelValue: {
            //
        },
        options: {
            default: () => [],
        },
    },

    emits: ['update:modelValue'],

    methods: {
        focus() {
            this.$refs.input.focus()
        }
    }
})
</script>
