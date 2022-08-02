<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type"
        class="inline-flex items-center px-4 py-2 bg-soketi1-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-soketi1-700 active:bg-soketi1-900 focus:outline-none focus:border-soketi1-900 focus:ring focus:ring-soketi1-300 disabled:opacity-25 transition"
    >
        <slot />
    </button>
</template>
