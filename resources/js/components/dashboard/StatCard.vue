<template>
    <div
        class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-5 flex items-start justify-between backdrop-blur-xl transition-all duration-300 hover:-translate-y-1 hover:border-slate-700/80 hover:shadow-xl hover:shadow-indigo-500/10 group relative overflow-hidden"
    >
        <!-- Background subtle glow gradient -->
        <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-indigo-500/5 rounded-full blur-2xl group-hover:bg-indigo-500/15 transition-all duration-300"></div>

        <div class="min-w-0 z-10">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ label }}</p>
            <p class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight bg-linear-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent truncate">
                {{ value }}
            </p>
            <p v-if="subtext" class="mt-2 text-xs font-medium text-slate-400 flex items-center gap-1.5 truncate">
                <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                <span>{{ subtext }}</span>
            </p>
        </div>

        <div
            class="shrink-0 w-12 h-12 rounded-xl flex items-center justify-center text-xl shadow-inner transition-transform duration-300 group-hover:scale-110 ring-1 ring-white/10 z-10"
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
    iconBg: { type: String, default: "bg-indigo-500/20 text-indigo-300" },
});
</script>

