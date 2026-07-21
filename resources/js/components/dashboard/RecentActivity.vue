<template>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">
                Recent Activity
            </h3>
            <div class="flex rounded-lg bg-gray-100 p-1 text-xs font-medium">
                <button
                    type="button"
                    class="px-3 py-1.5 rounded-md transition-colors"
                    :class="
                        tab === 'proposals'
                            ? 'bg-white text-indigo-600 shadow-sm'
                            : 'text-gray-500 hover:text-gray-700'
                    "
                    @click="tab = 'proposals'"
                >
                    Proposals
                </button>
                <button
                    type="button"
                    class="px-3 py-1.5 rounded-md transition-colors"
                    :class="
                        tab === 'invoices'
                            ? 'bg-white text-indigo-600 shadow-sm'
                            : 'text-gray-500 hover:text-gray-700'
                    "
                    @click="tab = 'invoices'"
                >
                    Invoices
                </button>
            </div>
        </div>

        <!-- Proposals tab -->
        <ul v-if="tab === 'proposals'" class="divide-y divide-gray-100">
            <li
                v-for="proposal in proposals"
                :key="proposal.id"
                class="py-3 first:pt-0 last:pb-0"
            >
                <router-link
                    :to="{
                        name: 'proposals.wizard',
                        params: { id: proposal.id },
                    }"
                    class="flex items-center justify-between gap-3 group"
                >
                    <div class="min-w-0">
                        <p
                            class="text-sm font-medium text-gray-800 truncate group-hover:text-indigo-600"
                        >
                            {{ proposal.title }}
                        </p>
                        <p class="text-xs text-gray-400 truncate">
                            {{ proposal.client?.name || "No client" }} ·
                            {{ formatDate(proposal.created_at) }}
                        </p>
                    </div>
                    <span
                        class="shrink-0 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium capitalize"
                        :class="proposalStatusStyles[proposal.status]"
                    >
                        {{ proposal.status }}
                    </span>
                </router-link>
            </li>
            <li v-if="!proposals.length" class="py-6 text-center text-sm text-gray-400">
                No proposals yet.
            </li>
        </ul>

        <!-- Invoices tab -->
        <ul v-else class="divide-y divide-gray-100">
            <li
                v-for="invoice in invoices"
                :key="invoice.id"
                class="py-3 first:pt-0 last:pb-0"
            >
                <router-link
                    :to="{ name: 'invoices.show', params: { id: invoice.id } }"
                    class="flex items-center justify-between gap-3 group"
                >
                    <div class="min-w-0">
                        <p
                            class="text-sm font-medium text-gray-800 truncate group-hover:text-indigo-600"
                        >
                            {{ invoice.invoice_number }}
                        </p>
                        <p class="text-xs text-gray-400 truncate">
                            {{ invoice.client?.name || "No client" }} ·
                            {{ formatDate(invoice.created_at) }}
                        </p>
                    </div>
                    <div class="shrink-0 text-right">
                        <p class="text-sm font-semibold text-gray-800">
                            {{ currency(invoice.total) }}
                        </p>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium capitalize"
                            :class="invoiceStatusStyles[invoice.status]"
                        >
                            {{ invoice.status.replace("_", " ") }}
                        </span>
                    </div>
                </router-link>
            </li>
            <li v-if="!invoices.length" class="py-6 text-center text-sm text-gray-400">
                No invoices yet.
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

// Same palette as components/invoices/StatusBadge.vue — kept consistent.
const invoiceStatusStyles = {
    unpaid: "bg-amber-50 text-amber-700",
    partially_paid: "bg-blue-50 text-blue-700",
    paid: "bg-emerald-50 text-emerald-700",
    overdue: "bg-red-50 text-red-700",
    cancelled: "bg-gray-100 text-gray-500",
};

const proposalStatusStyles = {
    draft: "bg-gray-100 text-gray-500",
    sent: "bg-blue-50 text-blue-700",
    accepted: "bg-emerald-50 text-emerald-700",
    rejected: "bg-red-50 text-red-700",
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
