<template>
    <div>
        <div>
            <span
                v-for="({ name }, tabIndex) in tabs"
                :key="`${tabIndex}-tab`"
                class="inline-block text-white px-4 py-3 cursor-pointer"
                :class="currentTabIndex === tabIndex ? 'bg-gray-900' : 'bg-gray-700'"
                @click="currentTabIndex = tabIndex"
            >
                {{ name }}
            </span>
        </div>

        <div
            v-for="({ id }, tabIndex) in tabs"
            v-show="tabIndex === currentTabIndex"
            :key="`${tabIndex}-content`"
        >
            <slot :name="id" />
        </div>
    </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
    props: {
        tabs: {
            default: () => [],
        },
    },

    data() {
        return {
            currentTabIndex: 0,
        };
    },
});
</script>
