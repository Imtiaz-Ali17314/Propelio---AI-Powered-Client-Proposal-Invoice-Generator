<template>
    <span
        class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold tracking-wider uppercase ring-1 shadow-sm"
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
    unpaid: "bg-amber-500/10 text-amber-400 ring-amber-500/30",
    partially_paid: "bg-blue-500/10 text-blue-400 ring-blue-500/30",
    paid: "bg-emerald-500/10 text-emerald-400 ring-emerald-500/30",
    overdue: "bg-rose-500/10 text-rose-400 ring-rose-500/30",
    cancelled: "bg-slate-800 text-slate-400 ring-slate-700",
};

const classes = computed(
    () => styles[props.status] || "bg-slate-800 text-slate-400 ring-slate-700",
);
const label = computed(() => props.status.replace("_", " "));
</script>

