<template>
    <AppLayout>
        <div v-if="store.loading" class="text-center py-16 text-gray-400">
            Loading...
        </div>

        <div v-else-if="invoice" class="max-w-3xl mx-auto space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">
                        {{ invoice.invoice_number }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ invoice.client?.name }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <StatusBadge :status="invoice.status" />
                    <button
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50"
                        @click="router.push(`/invoices/${invoice.id}/edit`)"
                    >
                        Edit
                    </button>
                    <button
                        class="px-3 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700"
                        @click="store.downloadPdf(invoice.id)"
                    >
                        Download PDF
                    </button>
                </div>
            </div>

            <!-- Summary -->
            <div
                class="bg-white border border-gray-200 rounded-xl p-5 grid grid-cols-4 gap-4 text-sm"
            >
                <div>
                    <p class="text-gray-400 text-xs uppercase mb-1">Total</p>
                    <p class="text-gray-900 font-semibold">
                        ${{ formatMoney(invoice.total) }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs uppercase mb-1">Paid</p>
                    <p class="text-emerald-600 font-semibold">
                        ${{ formatMoney(invoice.paid_amount) }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs uppercase mb-1">
                        Balance Due
                    </p>
                    <p class="text-red-600 font-semibold">
                        ${{ formatMoney(invoice.balance_due) }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400 text-xs uppercase mb-1">Due Date</p>
                    <p class="text-gray-900 font-semibold">
                        {{ formatDate(invoice.due_date) }}
                    </p>
                </div>
            </div>

            <!-- Line Items -->
            <div
                class="bg-white border border-gray-200 rounded-xl overflow-hidden"
            >
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="text-left px-5 py-2.5">Description</th>
                            <th class="text-right px-5 py-2.5">Qty</th>
                            <th class="text-right px-5 py-2.5">Rate</th>
                            <th class="text-right px-5 py-2.5">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="item in invoice.items" :key="item.id">
                            <td class="px-5 py-2.5">{{ item.description }}</td>
                            <td class="px-5 py-2.5 text-right">
                                {{ item.quantity }}
                            </td>
                            <td class="px-5 py-2.5 text-right">
                                ${{ formatMoney(item.rate) }}
                            </td>
                            <td class="px-5 py-2.5 text-right">
                                ${{ formatMoney(item.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div
                    class="px-5 py-3 border-t border-gray-100 flex justify-end"
                >
                    <div class="w-56 space-y-1 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>Subtotal</span
                            ><span>${{ formatMoney(invoice.subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>Tax</span
                            ><span>${{ formatMoney(invoice.tax) }}</span>
                        </div>
                        <div
                            class="flex justify-between font-semibold text-gray-900 pt-1 border-t border-gray-100"
                        >
                            <span>Total</span
                            ><span>${{ formatMoney(invoice.total) }}</span>
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
