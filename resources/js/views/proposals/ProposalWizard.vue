<!-- resources/js/views/proposals/ProposalWizard.vue -->
<template>
    <AppLayout>
        <div class="max-w-5xl mx-auto py-6 px-2 sm:px-4">
            <!-- Stepper Container -->
            <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-4 sm:p-6 mb-8 backdrop-blur-xl shadow-xl">
                <div class="flex items-center justify-between">
                    <template v-for="(step, index) in steps" :key="step.key">
                        <button
                            @click="goToStep(step.key)"
                            :disabled="!canNavigateTo(step.key)"
                            class="flex flex-col items-center gap-2 group shrink-0 transition-all"
                            :class="
                                canNavigateTo(step.key)
                                    ? 'cursor-pointer'
                                    : 'cursor-not-allowed opacity-35'
                            "
                        >
                            <div
                                class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl flex items-center justify-center font-bold text-xs sm:text-sm transition-all duration-300 shadow-md"
                                :class="stepCircleClass(step.key)"
                            >
                                <span v-if="isStepCompleted(step.key)">✓</span>
                                <span v-else>{{ index + 1 }}</span>
                            </div>
                            <span
                                class="hidden sm:inline text-xs font-semibold tracking-wide"
                                :class="
                                    currentStep === step.key
                                        ? 'text-indigo-400'
                                        : isStepCompleted(step.key)
                                        ? 'text-slate-300'
                                        : 'text-slate-500'
                                "
                            >
                                {{ step.label }}
                            </span>
                        </button>

                        <div
                            v-if="index < steps.length - 1"
                            class="flex-1 h-1 mx-2 rounded-full transition-colors duration-300"
                            :class="
                                isStepCompleted(step.key)
                                    ? 'bg-linear-to-r from-indigo-500 to-violet-500 shadow-sm shadow-indigo-500/50'
                                    : 'bg-slate-800'
                            "
                        ></div>
                    </template>
                </div>

                <!-- Current step label for mobile -->
                <div class="sm:hidden text-center text-xs font-bold uppercase tracking-wider text-indigo-400 mt-4 pt-3 border-t border-slate-800">
                    Step {{ steps.findIndex((s) => s.key === currentStep) + 1 }}: {{ steps.find((s) => s.key === currentStep)?.label }}
                </div>
            </div>

            <!-- Step Content Card -->
            <div
                class="bg-slate-900/80 rounded-2xl shadow-2xl border border-slate-800/80 p-6 sm:p-8 md:p-10 backdrop-blur-xl"
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
import { ref, computed, onMounted, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import AppLayout from "@/components/layout/AppLayout.vue";
import { useProposalsStore } from "@/stores/proposals";
import { useToast } from "@/composables/useToast";

import BriefStep from "@/components/proposals/BriefStep.vue";
import ScopeStep from "@/components/proposals/ScopeStep.vue";
import TimelineStep from "@/components/proposals/TimelineStep.vue";
import CostStep from "@/components/proposals/CostStep.vue";
import ReviewStep from "@/components/proposals/ReviewStep.vue";

const route = useRoute();
const router = useRouter();
const store = useProposalsStore();
const toast = useToast();

const steps = [
    { key: "brief", label: "Brief" },
    { key: "scope", label: "Scope" },
    { key: "timeline", label: "Timeline" },
    { key: "cost", label: "Cost Breakdown" },
    { key: "review", label: "Final Review" },
];

const currentStep = ref("brief");
const proposal = computed(() => store.currentProposal);

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
    const currentIndex = stepOrder.indexOf(proposal.value.generation_step);
    const targetIndex = stepOrder.indexOf(
        stepKey === "review" ? "completed" : stepKey,
    );
    return targetIndex <= currentIndex + 1;
}

function stepCircleClass(stepKey) {
    if (isStepCompleted(stepKey)) return "bg-emerald-500 text-slate-950 font-bold ring-2 ring-emerald-400/50 shadow-emerald-500/20";
    if (currentStep.value === stepKey)
        return "bg-gradient-to-tr from-indigo-600 to-violet-600 text-white ring-4 ring-indigo-500/30 shadow-indigo-500/40";
    return "bg-slate-800 text-slate-500 ring-1 ring-slate-700";
}

function goToStep(stepKey) {
    if (canNavigateTo(stepKey)) currentStep.value = stepKey;
}

async function handleBriefCreated(newProposal) {
    toast.success("Proposal created — let's generate the scope.");
    currentStep.value = "scope";
    router.replace({
        name: "proposals.wizard",
        params: { id: newProposal.id },
    });
}

async function handleGenerateScope() {
    try {
        await store.generateScope(proposal.value.id);
        toast.success("Scope generated successfully.");
    } catch (err) {
        toast.error(store.error || "Failed to generate scope. Please try again.");
    }
}

async function handleUpdateScope(scopeData) {
    try {
        await store.updateProposal(proposal.value.id, { scope: scopeData });
        toast.success("Scope saved.");
    } catch (err) {
        toast.error(store.error || "Failed to save scope.");
    }
}

async function handleGenerateTimeline() {
    try {
        await store.generateTimeline(proposal.value.id);
        toast.success("Timeline generated successfully.");
    } catch (err) {
        toast.error(store.error || "Failed to generate timeline. Please try again.");
    }
}

async function handleUpdateTimeline(timelineData) {
    try {
        await store.updateProposal(proposal.value.id, { timeline: timelineData });
        toast.success("Timeline saved.");
    } catch (err) {
        toast.error(store.error || "Failed to save timeline.");
    }
}

async function handleGenerateCost() {
    try {
        await store.generateCost(proposal.value.id);
        toast.success("Cost breakdown generated successfully.");
    } catch (err) {
        toast.error(store.error || "Failed to generate cost breakdown. Please try again.");
    }
}

async function handleUpdateCost(costData) {
    try {
        await store.updateProposal(proposal.value.id, { cost_breakdown: costData });
        toast.success("Cost breakdown saved.");
    } catch (err) {
        toast.error(store.error || "Failed to save cost breakdown.");
    }
}

async function handleFinish() {
    try {
        await store.updateProposal(proposal.value.id, {
            generation_step: "completed",
        });
        toast.success("Proposal completed! 🎉");
        currentStep.value = "review";
    } catch (err) {
        toast.error(store.error || "Failed to finish proposal.");
    }
}

async function initWizard() {
    if (route.params.id) {
        const loaded = await store.fetchProposal(route.params.id);
        const map = {
            brief: "brief",
            scope: "scope",
            timeline: "timeline",
            cost: "cost",
            completed: "review",
        };
        currentStep.value = map[loaded.generation_step] || "brief";
    } else {
        store.currentProposal = null;
        store.error = null;
        currentStep.value = "brief";
    }
}

onMounted(initWizard);

watch(
    () => route.params.id,
    () => initWizard(),
);
</script>
