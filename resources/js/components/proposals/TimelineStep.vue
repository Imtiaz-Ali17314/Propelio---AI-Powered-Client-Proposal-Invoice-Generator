<!-- resources/js/components/proposals/TimelineStep.vue -->
<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
            <h2 class="text-xl font-semibold text-gray-900">
                Project Timeline
            </h2>
            <button
                v-if="proposal?.timeline"
                @click="$emit('generate')"
                :disabled="generating"
                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1 disabled:opacity-50"
            >
                <span>↻</span>
                {{ generating ? "Regenerating..." : "Regenerate" }}
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-6">
            Scope ke basis pe AI ne ye timeline suggest ki hai — phases edit ya
            reorder kar sakte ho.
        </p>

        <!-- Empty state -->
        <div v-if="!proposal?.timeline" class="text-center py-12">
            <p class="text-gray-500 mb-4">Timeline abhi generate nahi hui.</p>
            <button
                @click="$emit('generate')"
                :disabled="generating"
                class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white font-medium px-6 py-3 rounded-lg transition-colors inline-flex items-center gap-2"
            >
                <span v-if="generating" class="animate-spin">⏳</span>
                {{
                    generating
                        ? "AI is thinking..."
                        : "✨ Generate Timeline with AI"
                }}
            </button>
        </div>

        <!-- Editable form -->
        <div v-else class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Total Duration</label
                >
                <input
                    v-model="local.total_duration"
                    type="text"
                    class="w-48 border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Phases</label
                >
                <div
                    v-for="(phase, index) in local.phases"
                    :key="index"
                    class="border border-gray-200 rounded-lg p-4 mb-3 space-y-2"
                >
                    <div class="flex flex-col sm:flex-row gap-2">
                        <input
                            v-model="phase.name"
                            type="text"
                            placeholder="Phase name"
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm font-medium focus:ring-2 focus:ring-indigo-500"
                        />
                        <div class="flex gap-2">
                            <input
                                v-model="phase.duration"
                                type="text"
                                placeholder="Duration"
                                class="flex-1 sm:w-28 sm:flex-none border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                            />
                            <button
                                @click="local.phases.splice(index, 1)"
                                class="text-red-400 hover:text-red-600 px-2 shrink-0"
                            >
                                ✕
                            </button>
                        </div>
                    </div>
                    <textarea
                        v-model="phase.description"
                        rows="2"
                        placeholder="Phase description"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 resize-none"
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
                    class="text-sm text-indigo-600 hover:text-indigo-700"
                >
                    + Add phase
                </button>
            </div>

            <div class="flex justify-between pt-4 border-t border-gray-100">
                <button
                    @click="$emit('back')"
                    class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2.5"
                >
                    ← Back
                </button>
                <button
                    @click="handleNext"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-lg transition-colors"
                >
                    Save & Continue →
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