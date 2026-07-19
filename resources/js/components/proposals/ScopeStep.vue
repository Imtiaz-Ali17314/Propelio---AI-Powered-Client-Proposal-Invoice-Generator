<!-- resources/js/components/proposals/ScopeStep.vue -->
<template>
    <div>
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-xl font-semibold text-gray-900">Project Scope</h2>
            <button
                v-if="proposal?.scope"
                @click="$emit('generate')"
                :disabled="generating"
                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1 disabled:opacity-50"
            >
                <span>↻</span>
                {{ generating ? "Regenerating..." : "Regenerate" }}
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-6">
            AI ne ye scope generate kiya hai brief ke basis pe — jo chaho edit
            kar sakte ho.
        </p>

        <!-- Empty state: pehli baar generate karna hai -->
        <div v-if="!proposal?.scope" class="text-center py-12">
            <p class="text-gray-500 mb-4">Scope abhi generate nahi hua.</p>
            <button
                @click="$emit('generate')"
                :disabled="generating"
                class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white font-medium px-6 py-3 rounded-lg transition-colors inline-flex items-center gap-2"
            >
                <span v-if="generating" class="animate-spin">⏳</span>
                {{
                    generating
                        ? "AI is thinking..."
                        : "✨ Generate Scope with AI"
                }}
            </button>
        </div>

        <!-- Editable form -->
        <div v-else class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Overview</label
                >
                <textarea
                    v-model="local.overview"
                    rows="3"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 resize-none"
                ></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Deliverables</label
                >
                <div
                    v-for="(item, index) in local.deliverables"
                    :key="index"
                    class="flex gap-2 mb-2"
                >
                    <input
                        v-model="local.deliverables[index]"
                        type="text"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    />
                    <button
                        @click="local.deliverables.splice(index, 1)"
                        class="text-red-400 hover:text-red-600 px-2"
                    >
                        ✕
                    </button>
                </div>
                <button
                    @click="local.deliverables.push('')"
                    class="text-sm text-indigo-600 hover:text-indigo-700"
                >
                    + Add deliverable
                </button>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Out of Scope</label
                >
                <div
                    v-for="(item, index) in local.out_of_scope"
                    :key="index"
                    class="flex gap-2 mb-2"
                >
                    <input
                        v-model="local.out_of_scope[index]"
                        type="text"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    />
                    <button
                        @click="local.out_of_scope.splice(index, 1)"
                        class="text-red-400 hover:text-red-600 px-2"
                    >
                        ✕
                    </button>
                </div>
                <button
                    @click="local.out_of_scope.push('')"
                    class="text-sm text-indigo-600 hover:text-indigo-700"
                >
                    + Add item
                </button>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
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
