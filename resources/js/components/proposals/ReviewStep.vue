<template>
    <div v-if="proposal">
        <div class="flex items-center justify-between mb-6 pb-5 border-b border-slate-800/80">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">
                    {{ proposal.title }}
                </h2>
                <p class="text-sm text-slate-400 mt-1 flex items-center gap-1.5">
                    <span>Prepared for</span>
                    <span class="font-semibold text-slate-200">{{ proposal.client?.name || "Client" }}</span>
                </p>
            </div>
            <span
                class="bg-emerald-500/15 text-emerald-400 text-xs font-bold px-3 py-1.5 rounded-full ring-1 ring-emerald-500/30 uppercase tracking-wider"
            >
                Ready to Send
            </span>
        </div>

        <div class="space-y-8">
            <!-- Overview -->
            <section class="bg-slate-950/40 rounded-2xl p-5 border border-slate-800/60">
                <h3
                    class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2.5"
                >
                    Project Overview
                </h3>
                <p class="text-slate-300 text-sm leading-relaxed">
                    {{ proposal.scope?.overview || 'No overview provided.' }}
                </p>
            </section>

            <!-- Deliverables -->
            <section class="bg-slate-950/40 rounded-2xl p-5 border border-slate-800/60">
                <h3
                    class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3"
                >
                    Key Deliverables
                </h3>
                <ul class="space-y-2">
                    <li
                        v-for="(item, i) in proposal.scope?.deliverables"
                        :key="i"
                        class="flex items-start gap-2.5 text-sm text-slate-300"
                    >
                        <span class="text-indigo-400 font-bold mt-0.5">✓</span>
                        <span>{{ item }}</span>
                    </li>
                </ul>
            </section>

            <!-- Timeline -->
            <section class="bg-slate-950/40 rounded-2xl p-5 border border-slate-800/60">
                <h3
                    class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center justify-between"
                >
                    <span>Milestone Timeline</span>
                    <span class="text-indigo-400 font-semibold text-xs tracking-normal normal-case"
                        >{{ proposal.timeline?.total_duration }}</span
                    >
                </h3>
                <div class="space-y-3.5 divide-y divide-slate-800/60">
                    <div
                        v-for="(phase, i) in proposal.timeline?.phases"
                        :key="i"
                        class="flex gap-4 pt-3.5 first:pt-0"
                    >
                        <div
                            class="w-28 shrink-0 text-xs font-bold text-indigo-400 bg-indigo-500/10 px-2.5 py-1 rounded-lg ring-1 ring-indigo-500/20 text-center self-start"
                        >
                            {{ phase.duration }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-100">
                                {{ phase.name }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5 leading-relaxed">
                                {{ phase.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Cost -->
            <section class="bg-slate-950/40 rounded-2xl p-5 border border-slate-800/60">
                <h3
                    class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3"
                >
                    Cost Breakdown
                </h3>
                <div class="border border-slate-800/80 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-slate-800/60">
                            <tr
                                v-for="(item, i) in proposal.cost_breakdown
                                    ?.items"
                                :key="i"
                                class="hover:bg-slate-800/20 transition-colors"
                            >
                                <td class="px-4 py-3 text-slate-300 font-medium">
                                    {{ item.label }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right text-slate-100 font-semibold tabular-nums"
                                >
                                    {{ currencySymbol
                                    }}{{ Number(item.amount).toLocaleString() }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-slate-900/90 border-t border-slate-800">
                            <tr>
                                <td
                                    class="px-4 py-3 font-bold text-white text-base"
                                >
                                    Total Amount
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-extrabold text-base bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent tabular-nums"
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

        <div class="pt-6 mt-8 border-t border-slate-800/80">
            <div
                v-if="proposal.invoices_count > 0"
                class="flex items-center gap-2 text-xs text-amber-400 bg-amber-500/10 border border-amber-500/30 rounded-xl px-4 py-2.5 mb-5"
            >
                <span>⚠️</span>
                <span>
                    This proposal already has
                    <strong>{{ proposal.invoices_count }}</strong>
                    invoice{{ proposal.invoices_count > 1 ? "s" : "" }}.
                </span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <button
                    @click="$emit('back')"
                    class="px-4 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition-all self-start sm:self-auto"
                >
                    ← Back to Edit
                </button>
                <div class="flex flex-wrap items-center gap-3">
                    <!-- Confirmation state replaces the button inline -->
                    <div
                        v-if="confirmingConvert"
                        class="flex items-center gap-2"
                    >
                        <span class="text-xs text-slate-400"
                            >Create another invoice?</span
                        >
                        <button
                            class="px-3 py-2 border border-slate-700 text-slate-300 rounded-xl text-sm hover:bg-slate-800"
                            @click="confirmingConvert = false"
                        >
                            Cancel
                        </button>
                        <button
                            class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-xl text-sm font-semibold hover:from-indigo-500 hover:to-violet-500"
                            :disabled="converting"
                            @click="convertToInvoice"
                        >
                            {{ converting ? "Creating..." : "Yes, Create" }}
                        </button>
                    </div>

                    <button
                        v-else
                        class="px-4 py-2.5 border border-indigo-500/50 text-indigo-400 hover:bg-indigo-500/10 rounded-xl text-sm font-semibold transition-all"
                        :disabled="converting"
                        @click="handleConvertClick"
                    >
                        {{
                            converting
                                ? "Converting..."
                                : proposal.invoices_count > 0
                                  ? "Create Another Invoice"
                                  : "Convert to Invoice"
                        }}
                    </button>

                    <button
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700/60 hover:bg-slate-800/60 hover:text-white transition-all duration-200"
                        @click="downloadPdf"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download PDF
                    </button>

                    <router-link
                        :to="{ name: 'proposals.list' }"
                        class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-600/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20"
                    >
                        <span>Save & Go to Hub</span>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from "vue";
import { useRouter } from "vue-router";
import { useInvoicesStore } from "@/stores/invoices";
import { useProposalsStore } from "@/stores/proposals";
import { useToast } from "@/composables/useToast";

const router = useRouter();
const invoicesStore = useInvoicesStore();
const proposalsStore = useProposalsStore();
const toast = useToast();
const converting = ref(false);
const confirmingConvert = ref(false);

const props = defineProps({
    proposal: Object,
});

defineEmits(["back"]);

const currencySymbols = { USD: "$", PKR: "₨", EUR: "€", GBP: "£" };
const currencySymbol = computed(
    () => currencySymbols[props.proposal?.cost_breakdown?.currency] || "",
);

function downloadPdf() {
    proposalsStore.downloadPdf(props.proposal.id);
}

function handleConvertClick() {
    // First invoice for this proposal — go straight through, no friction.
    if (!props.proposal.invoices_count) {
        convertToInvoice();
        return;
    }

    // Already has invoice(s) — show inline confirmation instead of creating immediately.
    confirmingConvert.value = true;
}

async function convertToInvoice() {
    converting.value = true;
    try {
        const invoice = await invoicesStore.convertFromProposal(
            props.proposal.id,
        );
        toast.success("Invoice created from proposal.");
        router.push(`/invoices/${invoice.id}`);
    } catch (e) {
        toast.error(
            invoicesStore.error || "Failed to convert proposal to invoice.",
        );
    } finally {
        converting.value = false;
        confirmingConvert.value = false;
    }
}
</script>
