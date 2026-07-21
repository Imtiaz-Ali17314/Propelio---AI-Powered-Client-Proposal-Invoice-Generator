<template>
    <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-6 backdrop-blur-xl h-full flex flex-col min-h-[350px] max-h-[370px]">
        <div class="flex items-center justify-between mb-4 shrink-0">
            <h3 class="text-base font-bold text-slate-100">
                Recent Activity
            </h3>
            <div class="flex rounded-xl bg-slate-950 p-1 text-xs font-semibold ring-1 ring-slate-800">
                <button
                    type="button"
                    class="px-3 py-1.5 rounded-lg transition-all duration-200"
                    :class="
                        tab === 'proposals'
                            ? 'bg-linear-to-r from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-600/30'
                            : 'text-slate-400 hover:text-slate-200'
                    "
                    @click="tab = 'proposals'"
                >
                    Proposals
                </button>
                <button
                    type="button"
                    class="px-3 py-1.5 rounded-lg transition-all duration-200"
                    :class="
                        tab === 'invoices'
                            ? 'bg-linear-to-r from-indigo-600 to-violet-600 text-white shadow-md shadow-indigo-600/30'
                            : 'text-slate-400 hover:text-slate-200'
                    "
                    @click="tab = 'invoices'"
                >
                    Invoices
                </button>
            </div>
        </div>

        <!-- Proposals tab -->
        <ul v-if="tab === 'proposals'" class="divide-y divide-slate-800/60 flex-1 overflow-y-auto no-scrollbar">
            <li
                v-for="proposal in proposals"
                :key="proposal.id"
                class="py-3.5 first:pt-0 last:pb-0"
            >
                <router-link
                    :to="{
                        name: 'proposals.wizard',
                        params: { id: proposal.id },
                    }"
                    class="flex items-center justify-between gap-3 group p-2 -mx-2 rounded-xl hover:bg-slate-800/40 transition-colors"
                >
                    <div class="min-w-0">
                        <p
                            class="text-sm font-semibold text-slate-200 truncate group-hover:text-indigo-400 transition-colors"
                        >
                            {{ proposal.title }}
                        </p>
                        <p class="text-xs text-slate-400 truncate mt-0.5">
                            {{ proposal.client?.name || "No client" }} ·
                            {{ formatDate(proposal.created_at) }}
                        </p>
                    </div>
                    <span
                        class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold capitalize ring-1"
                        :class="proposalStatusStyles[proposal.status]"
                    >
                        {{ proposal.status }}
                    </span>
                </router-link>
            </li>
            <li v-if="!proposals.length" class="py-8 text-center text-xs text-slate-400">
                No proposals recorded yet.
            </li>
        </ul>

        <!-- Invoices tab -->
        <ul v-else class="divide-y divide-slate-800/60 flex-1 overflow-y-auto no-scrollbar">
            <li
                v-for="invoice in invoices"
                :key="invoice.id"
                class="py-3.5 first:pt-0 last:pb-0"
            >
                <router-link
                    :to="{ name: 'invoices.show', params: { id: invoice.id } }"
                    class="flex items-center justify-between gap-3 group p-2 -mx-2 rounded-xl hover:bg-slate-800/40 transition-colors"
                >
                    <div class="min-w-0">
                        <p
                            class="text-sm font-semibold text-slate-200 truncate group-hover:text-indigo-400 transition-colors"
                        >
                            {{ invoice.invoice_number }}
                        </p>
                        <p class="text-xs text-slate-400 truncate mt-0.5">
                            {{ invoice.client?.name || "No client" }} ·
                            {{ formatDate(invoice.created_at) }}
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-sm font-bold text-slate-100">
                            {{ currency(invoice.total) }}
                        </p>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold capitalize ring-1 mt-0.5"
                            :class="invoiceStatusStyles[invoice.status]"
                        >
                            {{ invoice.status.replace("_", " ") }}
                        </span>
                    </div>
                </router-link>
            </li>
            <li v-if="!invoices.length" class="py-8 text-center text-xs text-slate-400">
                No invoices issued yet.
            </li>
        </ul>
    </div>
</template>

<script setup>
import { ref } from "vue";

defineProps({
    proposals: { type: Array, default: () => [] },
    invoices: { type: Array, default: () => [] },
});

const tab = ref("proposals");

const invoiceStatusStyles = {
    unpaid: "bg-amber-500/10 text-amber-400 ring-amber-500/30",
    partially_paid: "bg-blue-500/10 text-blue-400 ring-blue-500/30",
    paid: "bg-emerald-500/10 text-emerald-400 ring-emerald-500/30",
    overdue: "bg-rose-500/10 text-rose-400 ring-rose-500/30",
    cancelled: "bg-slate-800 text-slate-400 ring-slate-700",
};

const proposalStatusStyles = {
    draft: "bg-slate-800 text-slate-400 ring-slate-700",
    sent: "bg-blue-500/10 text-blue-400 ring-blue-500/30",
    accepted: "bg-emerald-500/10 text-emerald-400 ring-emerald-500/30",
    rejected: "bg-rose-500/10 text-rose-400 ring-rose-500/30",
};

function currency(amount) {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(amount || 0);
}

function formatDate(dateStr) {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
    });
}
</script>

