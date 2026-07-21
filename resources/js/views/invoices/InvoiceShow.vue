<template>
    <AppLayout>
        <!-- Loading State -->
        <div v-if="store.loading" class="max-w-3xl mx-auto py-6 px-2 sm:px-4 space-y-6">
            <div class="h-14 bg-slate-900/50 border border-slate-800/50 rounded-2xl animate-pulse shimmer-ai"></div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div v-for="n in 4" :key="n" class="h-20 bg-slate-900/50 border border-slate-800/50 rounded-2xl animate-pulse shimmer-ai"></div>
            </div>
            <div class="h-48 bg-slate-900/50 border border-slate-800/50 rounded-2xl animate-pulse"></div>
        </div>

        <div v-else-if="invoice" class="max-w-3xl mx-auto py-6 px-2 sm:px-4 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                            {{ invoice.invoice_number }}
                        </h1>
                        <StatusBadge :status="invoice.status" />
                    </div>
                    <p class="text-sm text-slate-400 mt-1 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        {{ invoice.client?.name }}
                    </p>
                </div>
                <div class="flex items-center gap-2.5 self-start sm:self-auto">
                    <button
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium text-slate-300 border border-slate-700/60 hover:bg-slate-800/60 hover:text-white transition-all duration-200"
                        @click="router.push(`/invoices/${invoice.id}/edit`)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>
                    <button
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-linear-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-600/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20"
                        @click="store.downloadPdf(invoice.id)"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download PDF
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 backdrop-blur-xl grid grid-cols-2 sm:grid-cols-4 gap-5">
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Total</p>
                    <p class="text-xl font-extrabold bg-linear-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent tabular-nums">
                        ${{ formatMoney(invoice.total) }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Paid</p>
                    <p class="text-xl font-extrabold text-emerald-400 tabular-nums">
                        ${{ formatMoney(invoice.paid_amount) }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Balance Due</p>
                    <p class="text-xl font-extrabold text-rose-400 tabular-nums">
                        ${{ formatMoney(invoice.balance_due) }}
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1.5">Due Date</p>
                    <p class="text-xl font-extrabold text-slate-200">
                        {{ formatDate(invoice.due_date) }}
                    </p>
                </div>
            </div>

            <!-- Line Items Table -->
            <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-xl">
                <div class="overflow-x-auto">
                <table class="w-full text-sm min-w-125">
                    <thead>
                        <tr class="border-b border-slate-800/60">
                            <th class="text-left px-5 py-3.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Description</th>
                            <th class="text-right px-5 py-3.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Qty</th>
                            <th class="text-right px-5 py-3.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Rate</th>
                            <th class="text-right px-5 py-3.5 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/40">
                        <tr v-for="item in invoice.items" :key="item.id" class="hover:bg-slate-800/30 transition-colors">
                            <td class="px-5 py-3.5 text-slate-200 font-medium">{{ item.description }}</td>
                            <td class="px-5 py-3.5 text-right text-slate-400 tabular-nums">
                                {{ item.quantity }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-slate-400 tabular-nums">
                                ${{ formatMoney(item.rate) }}
                            </td>
                            <td class="px-5 py-3.5 text-right text-slate-200 font-semibold tabular-nums">
                                ${{ formatMoney(item.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>

                <!-- Summary Footer -->
                <div class="px-5 py-4 border-t border-slate-800/60 flex justify-end">
                    <div class="w-64 bg-slate-950/60 rounded-xl p-4 ring-1 ring-slate-800/60 space-y-2.5">
                        <div class="flex justify-between text-sm text-slate-400">
                            <span>Subtotal</span>
                            <span class="font-medium text-slate-300 tabular-nums">${{ formatMoney(invoice.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-sm text-slate-400">
                            <span>Tax</span>
                            <span class="font-medium text-slate-300 tabular-nums">${{ formatMoney(invoice.tax) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-white pt-2.5 border-t border-slate-700/50">
                            <span>Total</span>
                            <span class="text-lg bg-linear-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent tabular-nums">${{ formatMoney(invoice.total) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <PaymentHistory
                :invoice="invoice"
                @recorded="refresh"
                @deleted="refresh"
            />
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import AppLayout from "@/components/layout/AppLayout.vue";
import StatusBadge from "@/components/invoices/StatusBadge.vue";
import PaymentHistory from "@/components/invoices/PaymentHistory.vue";
import { useInvoicesStore } from "@/stores/invoices";

const route = useRoute();
const router = useRouter();
const store = useInvoicesStore();

const invoice = computed(() => store.currentInvoice);

function formatDate(date) {
    if (!date) return "—";
    return new Date(date).toLocaleDateString("en-US", {
        day: "numeric",
        month: "short",
        year: "numeric",
    });
}

function formatMoney(value) {
    return Number(value ?? 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function refresh() {
    store.fetchOne(route.params.id);
}

onMounted(refresh);
</script>
