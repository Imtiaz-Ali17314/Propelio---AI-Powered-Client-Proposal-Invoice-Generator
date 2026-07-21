<template>
    <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 backdrop-blur-xl">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-sm ring-1 ring-emerald-500/25">💳</div>
                <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wider">Payment History</h2>
            </div>
            <button
                v-if="
                    invoice.status !== 'cancelled' && invoice.status !== 'paid'
                "
                class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg transition-all duration-200"
                :class="showForm ? 'text-rose-400 hover:text-rose-300 hover:bg-rose-500/10' : 'text-indigo-400 hover:text-indigo-300 hover:bg-indigo-500/10'"
                @click="showForm = !showForm"
            >
                <svg v-if="!showForm" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ showForm ? "Cancel" : "Record Payment" }}
            </button>
        </div>

        <form
            v-if="showForm"
            @submit.prevent="handleSubmit"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5 mb-1 items-start bg-slate-950/40 rounded-xl p-4 ring-1 ring-slate-800/60"
        >
            <div>
                <input
                    v-model.number="form.amount"
                    type="number"
                    min="0.01"
                    :max="invoice.balance_due"
                    step="0.01"
                    placeholder="Amount"
                    required
                    class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200"
                />
            </div>
            <input
                v-model="form.paid_at"
                type="date"
                required
                class="bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200 scheme:dark"
            />
            <select
                v-model="form.method"
                class="bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200 appearance-none"
            >
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="other">Other</option>
            </select>
            <button
                type="submit"
                :disabled="store.saving"
                class="bg-linear-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-xl px-3.5 py-2.5 text-sm font-semibold shadow-lg shadow-indigo-600/20 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20 disabled:opacity-50"
            >
                Save
            </button>
        </form>
        <p v-if="showForm" class="text-xs text-slate-500 mb-4 px-1">
            Remaining balance:
            <span class="text-slate-300 font-semibold"
                >${{ formatMoney(invoice.balance_due) }}</span
            >
        </p>

        <div v-if="invoice.payments?.length" class="divide-y divide-slate-800/40">
            <div
                v-for="payment in invoice.payments"
                :key="payment.id"
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-3 text-sm group"
            >
                <div class="flex flex-wrap items-center gap-x-2">
                    <span class="text-emerald-400 font-bold tabular-nums"
                        >${{ formatMoney(payment.amount) }}</span
                    >
                    <span class="text-slate-600">·</span>
                    <span class="text-slate-400 capitalize px-2 py-0.5 bg-slate-800/60 rounded-md text-xs font-medium">{{
                        payment.method.replace("_", " ")
                    }}</span>
                    <span class="text-slate-600">·</span>
                    <span class="text-slate-500 text-xs">{{
                        formatDate(payment.paid_at)
                    }}</span>
                </div>
                <button
                    class="text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 text-xs font-medium px-2 py-1 rounded-lg transition-all duration-200 opacity-0 group-hover:opacity-100"
                    @click="handleDelete(payment.id)"
                >
                    Remove
                </button>
            </div>
        </div>
        <p v-else class="text-sm text-slate-500 py-3 text-center">
            No payments recorded yet.
        </p>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useInvoicesStore } from "@/stores/invoices";
import { useToast } from "@/composables/useToast";

const props = defineProps({
    invoice: { type: Object, required: true },
});

const emit = defineEmits(["recorded", "deleted"]);

const store = useInvoicesStore();
const toast = useToast();
const showForm = ref(false);

const form = reactive({
    amount: "",
    paid_at: new Date().toISOString().slice(0, 10),
    method: "bank_transfer",
});

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

async function handleSubmit() {
    if (form.amount > props.invoice.balance_due) {
        toast.error(
            `Payment cannot exceed the remaining balance of $${formatMoney(props.invoice.balance_due)}.`,
        );
        return;
    }

    try {
        await store.recordPayment(props.invoice.id, form);
        toast.success("Payment recorded successfully.");
        form.amount = "";
        showForm.value = false;
        emit("recorded");
    } catch (e) {
        toast.error(store.error || "Failed to record payment.");
    }
}

async function handleDelete(paymentId) {
    if (!confirm("Remove this payment record?")) return;
    try {
        await store.deletePayment(props.invoice.id, paymentId);
        toast.success("Payment record removed.");
        emit("deleted");
    } catch (e) {
        toast.error(store.error || "Failed to delete payment.");
    }
}
</script>