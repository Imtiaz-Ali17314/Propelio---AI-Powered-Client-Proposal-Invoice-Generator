<template>
    <span
        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium capitalize"
        :class="classes"
    >
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    status: { type: String, required: true },
});

const styles = {
    unpaid: "bg-amber-50 text-amber-700",
    partially_paid: "bg-blue-50 text-blue-700",
    paid: "bg-emerald-50 text-emerald-700",
    overdue: "bg-red-50 text-red-700",
    cancelled: "bg-gray-100 text-gray-500",
};

const classes = computed(
    () => styles[props.status] || "bg-gray-100 text-gray-500",
);
const label = computed(() => props.status.replace("_", " "));
</script>
