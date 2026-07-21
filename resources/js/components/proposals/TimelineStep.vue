<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">
                Project Timeline
            </h2>
            <button
                v-if="proposal?.timeline"
                @click="$emit('generate')"
                :disabled="generating"
                class="text-xs sm:text-sm text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 ring-1 ring-indigo-500/30 transition-all disabled:opacity-50"
            >
                <span :class="{ 'animate-spin': generating }">↻</span>
                {{ generating ? "Regenerating Timeline..." : "Regenerate AI Timeline" }}
            </button>
        </div>
        <p class="text-xs sm:text-sm text-slate-400 mb-6">
            AI suggested milestone timeline based on project scope — customize phases and estimated durations.
        </p>

        <!-- Empty state -->
        <div v-if="!proposal?.timeline" class="text-center py-12 bg-slate-950/40 rounded-2xl border border-dashed border-slate-800 p-8">
            <p class="text-slate-400 mb-4 text-sm">Timeline has not been generated yet.</p>
            <button
                @click="$emit('generate')"
                :disabled="generating"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold px-6 py-3 rounded-xl shadow-lg shadow-indigo-600/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20 disabled:opacity-50"
            >
                <span v-if="generating" class="animate-spin">⏳</span>
                <span>{{
                    generating
                        ? "AI is structuring timeline..."
                        : "✨ Generate Timeline with AI"
                }}</span>
            </button>
        </div>

        <!-- Editable form -->
        <div v-else class="space-y-6">
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2"
                    >Estimated Total Duration</label
                >
                <input
                    v-model="local.total_duration"
                    type="text"
                    placeholder="e.g. 4 Weeks or 2 Months"
                    class="w-full sm:w-64 bg-slate-950/80 border border-slate-700/60 rounded-xl px-4 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all"
                />
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3"
                    >Project Phases</label
                >
                <div
                    v-for="(phase, index) in local.phases"
                    :key="index"
                    class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-4 sm:p-5 mb-3.5 space-y-3 ring-1 ring-white/5"
                >
                    <div class="flex flex-col sm:flex-row gap-2.5">
                        <input
                            v-model="phase.name"
                            type="text"
                            placeholder="Phase Name (e.g. Discovery & Wireframing)"
                            class="flex-1 bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm font-semibold text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                        />
                        <div class="flex gap-2">
                            <input
                                v-model="phase.duration"
                                type="text"
                                placeholder="Duration (e.g. 1 Week)"
                                class="flex-1 sm:w-36 sm:flex-none bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                            />
                            <button
                                @click="local.phases.splice(index, 1)"
                                class="text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 w-9 h-9 flex items-center justify-center rounded-xl transition-all shrink-0"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                    <textarea
                        v-model="phase.description"
                        rows="2"
                        placeholder="Detail key activities included in this phase..."
                        class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 resize-none"
                    ></textarea>
                </div>
                <button
                    @click="
                        local.phases.push({
                            name: '',
                            duration: '',
                            description: '',
                        })
                    "
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-400 hover:text-indigo-300 px-3 py-1.5 rounded-lg hover:bg-indigo-500/10 transition-all"
                >
                    + Add Phase
                </button>
            </div>

            <div class="flex items-center justify-between pt-5 border-t border-slate-800/80">
                <button
                    @click="$emit('back')"
                    class="px-4 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition-all"
                >
                    ← Back
                </button>
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

const emit = defineEmits(["generate", "next", "back", "update"]);

const local = reactive({
    total_duration: "",
    phases: [],
});

watch(
    () => props.proposal?.timeline,
    (newTimeline) => {
        if (newTimeline) {
            local.total_duration = newTimeline.total_duration || "";
            local.phases = (newTimeline.phases || []).map((p) => ({ ...p }));
        }
    },
    { immediate: true, deep: true },
);

async function handleNext() {
    await emit("update", {
        total_duration: local.total_duration,
        phases: local.phases.filter((p) => p.name?.trim()),
    });
    emit("next");
}
</script>