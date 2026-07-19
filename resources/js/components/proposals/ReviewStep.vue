<!-- resources/js/components/proposals/ReviewStep.vue -->
<template>
    <div v-if="proposal">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ proposal.title }}
                </h2>
                <p class="text-sm text-gray-500">
                    Prepared for {{ proposal.client?.name }}
                </p>
            </div>
            <span
                class="bg-emerald-100 text-emerald-700 text-xs font-medium px-3 py-1 rounded-full"
            >
                Ready to send
            </span>
        </div>

        <div class="space-y-8">
            <!-- Overview -->
            <section>
                <h3
                    class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-2"
                >
                    Overview
                </h3>
                <p class="text-gray-700 text-sm leading-relaxed">
                    {{ proposal.scope?.overview }}
                </p>
            </section>

            <!-- Deliverables -->
            <section>
                <h3
                    class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-2"
                >
                    Deliverables
                </h3>
                <ul class="space-y-1.5">
                    <li
                        v-for="(item, i) in proposal.scope?.deliverables"
                        :key="i"
                        class="flex items-start gap-2 text-sm text-gray-700"
                    >
                        <span class="text-indigo-500 mt-0.5">✓</span> {{ item }}
                    </li>
                </ul>
            </section>

            <!-- Timeline -->
            <section>
                <h3
                    class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3"
                >
                    Timeline
                    <span class="text-gray-400 font-normal normal-case"
                        >— {{ proposal.timeline?.total_duration }}</span
                    >
                </h3>
                <div class="space-y-3">
                    <div
                        v-for="(phase, i) in proposal.timeline?.phases"
                        :key="i"
                        class="flex gap-4"
                    >
                        <div
                            class="w-24 flex-shrink-0 text-xs font-medium text-indigo-600 pt-0.5"
                        >
                            {{ phase.duration }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ phase.name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ phase.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Cost -->
            <section>
                <h3
                    class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3"
                >
                    Cost Breakdown
                </h3>
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <table class="w-full text-sm">
                        <tbody>
                            <tr
                                v-for="(item, i) in proposal.cost_breakdown
                                    ?.items"
                                :key="i"
                                class="border-b border-gray-100 last:border-0"
                            >
                                <td class="px-4 py-2.5 text-gray-700">
                                    {{ item.label }}
                                </td>
                                <td
                                    class="px-4 py-2.5 text-right text-gray-900 font-medium"
                                >
                                    {{ currencySymbol
                                    }}{{ Number(item.amount).toLocaleString() }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td
                                    class="px-4 py-2.5 font-semibold text-gray-900"
                                >
                                    Total
                                </td>
                                <td
                                    class="px-4 py-2.5 text-right font-semibold text-gray-900"
                                >
                                    {{ currencySymbol
                                    }}{{
                                        Number(
                                            proposal.cost_breakdown?.total,
                                        ).toLocaleString()
                                    }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        </div>

        <div class="flex justify-between pt-8 mt-8 border-t border-gray-100">
            <button
                @click="$emit('back')"
                class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2.5"
            >
                ← Back to edit
            </button>
            <div class="flex gap-3">
                <button
                    class="border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium px-5 py-2.5 rounded-lg transition-colors"
                    disabled
                    title="Coming in the next step — PDF export"
                >
                    📄 Export PDF
                </button>
                <router-link
                    :to="{ name: 'proposals.list' }"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2.5 rounded-lg transition-colors"
                >
                    Save & Go to List
                </router-link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    proposal: Object,
});

defineEmits(["back"]);

const currencySymbols = { USD: "$", PKR: "₨", EUR: "€", GBP: "£" };
const currencySymbol = computed(
    () => currencySymbols[props.proposal?.cost_breakdown?.currency] || "",
);
</script>
