<template>
    <div
        class="bg-white rounded-xl border border-gray-200 p-5 flex items-start justify-between"
    >
        <div class="min-w-0">
            <p class="text-sm font-medium text-gray-500">{{ label }}</p>
            <p class="mt-2 text-2xl font-bold text-gray-900 truncate">
                {{ value }}
            </p>
            <p v-if="subtext" class="mt-1 text-xs text-gray-400">
                {{ subtext }}
            </p>
        </div>
        <div
            class="shrink-0 w-11 h-11 rounded-lg flex items-center justify-center text-xl"
            :class="iconBg"
        >
            {{ icon }}
        </div>
    </div>
</template>

<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
    subtext: { type: String, default: "" },
    icon: { type: String, default: "📊" },
    iconBg: { type: String, default: "bg-indigo-50" },
});
</script>
