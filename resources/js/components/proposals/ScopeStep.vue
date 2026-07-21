<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Project Scope</h2>
            <button
                v-if="proposal?.scope"
                @click="$emit('generate')"
                :disabled="generating"
                class="text-xs sm:text-sm text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 ring-1 ring-indigo-500/30 transition-all disabled:opacity-50"
            >
                <span :class="{ 'animate-spin': generating }">↻</span>
                {{ generating ? "Regenerating Scope..." : "Regenerate AI Scope" }}
            </button>
        </div>
        <p class="text-xs sm:text-sm text-slate-400 mb-6">
            AI generated project scope based on client brief — review and customize deliverables below.
        </p>

        <!-- Empty state -->
        <div v-if="!proposal?.scope" class="text-center py-12 bg-slate-950/40 rounded-2xl border border-dashed border-slate-800 p-8">
            <p class="text-slate-400 mb-4 text-sm">Scope has not been generated yet.</p>
            <button
                @click="$emit('generate')"
                :disabled="generating"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold px-6 py-3 rounded-xl shadow-lg shadow-indigo-600/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20 disabled:opacity-50"
            >
                <span v-if="generating" class="animate-spin">⏳</span>
                <span>{{
                    generating
                        ? "AI is analyzing brief..."
                        : "✨ Generate Scope with AI"
                }}</span>
            </button>
        </div>

        <!-- Editable form -->
        <div v-else class="space-y-6">
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2"
                    >Project Overview</label
                >
                <textarea
                    v-model="local.overview"
                    rows="4"
                    class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all resize-none"
                    placeholder="Enter high-level project summary..."
                ></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2"
                    >Deliverables</label
                >
                <div
                    v-for="(item, index) in local.deliverables"
                    :key="index"
                    class="flex items-center gap-2 mb-2.5"
                >
                    <input
                        v-model="local.deliverables[index]"
                        type="text"
                        placeholder="e.g. Responsive Web Application UI Design"
                        class="flex-1 bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                    />
                    <button
                        @click="local.deliverables.splice(index, 1)"
                        class="text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 w-9 h-9 flex items-center justify-center rounded-xl transition-all shrink-0"
                    >
                        ✕
                    </button>
                </div>
                <button
                    @click="local.deliverables.push('')"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-400 hover:text-indigo-300 px-3 py-1.5 rounded-lg hover:bg-indigo-500/10 transition-all"
                >
                    + Add Deliverable
                </button>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2"
                    >Out of Scope</label
                >
                <div
                    v-for="(item, index) in local.out_of_scope"
                    :key="index"
                    class="flex items-center gap-2 mb-2.5"
                >
                    <input
                        v-model="local.out_of_scope[index]"
                        type="text"
                        placeholder="e.g. Domain registration and third-party hosting fees"
                        class="flex-1 bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                    />
                    <button
                        @click="local.out_of_scope.splice(index, 1)"
                        class="text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 w-9 h-9 flex items-center justify-center rounded-xl transition-all shrink-0"
                    >
                        ✕
                    </button>
                </div>
                <button
                    @click="local.out_of_scope.push('')"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-400 hover:text-indigo-300 px-3 py-1.5 rounded-lg hover:bg-indigo-500/10 transition-all"
                >
                    + Add Item
                </button>
            </div>

            <div class="flex justify-end pt-5 border-t border-slate-800/80">
                <button
                    @click="handleNext"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20"
                >
                    <span>Save & Continue</span>
                    <span>→</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, watch } from "vue";

const props = defineProps({
    proposal: Object,
    generating: Boolean,
});

const emit = defineEmits(["generate", "next", "update"]);

const local = reactive({
    overview: "",
    deliverables: [],
    out_of_scope: [],
});

watch(
    () => props.proposal?.scope,
    (newScope) => {
        if (newScope) {
            local.overview = newScope.overview || "";
            local.deliverables = [...(newScope.deliverables || [])];
            local.out_of_scope = [...(newScope.out_of_scope || [])];
        }
    },
    { immediate: true, deep: true },
);

async function handleNext() {
    await emit("update", {
        overview: local.overview,
        deliverables: local.deliverables.filter((d) => d.trim()),
        out_of_scope: local.out_of_scope.filter((d) => d.trim()),
    });
    emit("next");
}
</script>