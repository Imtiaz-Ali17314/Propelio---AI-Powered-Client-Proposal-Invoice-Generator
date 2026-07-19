<!-- resources/js/views/proposals/ProposalWizard.vue -->
<template>
    <AppLayout>
        <div class="max-w-4xl mx-auto py-8 px-4">
            <!-- Stepper -->
            <div class="flex items-center justify-between mb-10">
                <template v-for="(step, index) in steps" :key="step.key">
                    <button
                        @click="goToStep(step.key)"
                        :disabled="!canNavigateTo(step.key)"
                        class="flex flex-col items-center gap-2 group"
                        :class="
                            canNavigateTo(step.key)
                                ? 'cursor-pointer'
                                : 'cursor-not-allowed opacity-40'
                        "
                    >
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-semibold text-sm transition-colors"
                            :class="stepCircleClass(step.key)"
                        >
                            <span v-if="isStepCompleted(step.key)">✓</span>
                            <span v-else>{{ index + 1 }}</span>
                        </div>
                        <span
                            class="text-xs font-medium"
                            :class="
                                currentStep === step.key
                                    ? 'text-indigo-600'
                                    : 'text-gray-500'
                            "
                        >
                            {{ step.label }}
                        </span>
                    </button>

                    <div
                        v-if="index < steps.length - 1"
                        class="flex-1 h-0.5 mx-2"
                        :class="
                            isStepCompleted(step.key)
                                ? 'bg-indigo-600'
                                : 'bg-gray-200'
                        "
                    ></div>
                </template>
            </div>

            <!-- Error Banner -->
            <div
                v-if="store.error"
                class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm"
            >
                {{ store.error }}
            </div>

            <!-- Step Content -->
            <div
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8"
            >
                <BriefStep
                    v-if="currentStep === 'brief'"
                    @created="handleBriefCreated"
                />

                <ScopeStep
                    v-else-if="currentStep === 'scope'"
                    :proposal="proposal"
                    :generating="store.generating"
                    @generate="handleGenerateScope"
                    @next="currentStep = 'timeline'"
                    @update="handleUpdateScope"
                />

                <TimelineStep
                    v-else-if="currentStep === 'timeline'"
                    :proposal="proposal"
                    :generating="store.generating"
                    @generate="handleGenerateTimeline"
                    @next="currentStep = 'cost'"
                    @back="currentStep = 'scope'"
                    @update="handleUpdateTimeline"
                />

                <CostStep
                    v-else-if="currentStep === 'cost'"
                    :proposal="proposal"
                    :generating="store.generating"
                    @generate="handleGenerateCost"
                    @next="handleFinish"
                    @back="currentStep = 'timeline'"
                    @update="handleUpdateCost"
                />

                <ReviewStep
                    v-else-if="currentStep === 'review'"
                    :proposal="proposal"
                    @back="currentStep = 'cost'"
                />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import AppLayout from "@/components/layout/AppLayout.vue";
import { useProposalsStore } from "@/stores/proposals";

import BriefStep from "@/components/proposals/BriefStep.vue";
import ScopeStep from "@/components/proposals/ScopeStep.vue";
import TimelineStep from "@/components/proposals/TimelineStep.vue";
import CostStep from "@/components/proposals/CostStep.vue";
import ReviewStep from "@/components/proposals/ReviewStep.vue";

const route = useRoute();
const router = useRouter();
const store = useProposalsStore();

const steps = [
    { key: "brief", label: "Brief" },
    { key: "scope", label: "Scope" },
    { key: "timeline", label: "Timeline" },
    { key: "cost", label: "Cost" },
    { key: "review", label: "Review" },
];

const currentStep = ref("brief");
const proposal = computed(() => store.currentProposal);

// generation_step order map — kis step tak pahunch chuke hain
const stepOrder = ["brief", "scope", "timeline", "cost", "completed"];

function isStepCompleted(stepKey) {
    if (!proposal.value) return false;
    const currentIndex = stepOrder.indexOf(proposal.value.generation_step);
    const targetIndex = stepOrder.indexOf(stepKey);
    return (
        targetIndex < currentIndex ||
        (stepKey === "review" && proposal.value.generation_step === "cost")
    );
}

function canNavigateTo(stepKey) {
    if (stepKey === "brief") return true;
    if (!proposal.value) return false;
    // User sirf un steps pe ja sakta hai jo already generate ho chuke hain, ya jo current hai
    const currentIndex = stepOrder.indexOf(proposal.value.generation_step);
    const targetIndex = stepOrder.indexOf(
        stepKey === "review" ? "completed" : stepKey,
    );
    return targetIndex <= currentIndex + 1;
}

function stepCircleClass(stepKey) {
    if (isStepCompleted(stepKey)) return "bg-indigo-600 text-white";
    if (currentStep.value === stepKey)
        return "bg-indigo-100 text-indigo-600 ring-2 ring-indigo-600";
    return "bg-gray-100 text-gray-400";
}

function goToStep(stepKey) {
    if (canNavigateTo(stepKey)) currentStep.value = stepKey;
}

async function handleBriefCreated(newProposal) {
    currentStep.value = "scope";
    // Router URL update kar do taake refresh pe proposal load ho sake
    router.replace({
        name: "proposals.wizard",
        params: { id: newProposal.id },
    });
}

async function handleGenerateScope() {
    await store.generateScope(proposal.value.id);
}

async function handleUpdateScope(scopeData) {
    await store.updateProposal(proposal.value.id, { scope: scopeData });
}

async function handleGenerateTimeline() {
    await store.generateTimeline(proposal.value.id);
}

async function handleUpdateTimeline(timelineData) {
    await store.updateProposal(proposal.value.id, { timeline: timelineData });
}

async function handleGenerateCost() {
    await store.generateCost(proposal.value.id);
}

async function handleUpdateCost(costData) {
    await store.updateProposal(proposal.value.id, { cost_breakdown: costData });
}

async function handleFinish() {
    await store.updateProposal(proposal.value.id, {
        generation_step: "completed",
    });
    currentStep.value = "review";
}

onMounted(async () => {
    if (route.params.id) {
        const loaded = await store.fetchProposal(route.params.id);
        // Existing proposal resume karo uske generation_step ke hisaab se
        const map = {
            brief: "brief",
            scope: "scope",
            timeline: "timeline",
            cost: "cost",
            completed: "review",
        };
        currentStep.value = map[loaded.generation_step] || "brief";
    }
});
</script>
