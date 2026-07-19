<template>
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-900">Payment History</h2>
            <button
                v-if="
                    invoice.status !== 'cancelled' && invoice.status !== 'paid'
                "
                class="text-sm text-indigo-600 font-medium hover:underline"
                @click="showForm = !showForm"
            >
                {{ showForm ? "Cancel" : "+ Record Payment" }}
            </button>
        </div>

        <form
            v-if="showForm"
            @submit.prevent="handleSubmit"
            class="grid grid-cols-4 gap-2 mb-4 items-start"
        >
            <input
                v-model.number="form.amount"
                type="number"
                min="0.01"
                step="0.01"
                placeholder="Amount"
                required
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            />
            <input
                v-model="form.paid_at"
                type="date"
                required
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            />
            <select
                v-model="form.method"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >
                <option value="bank_transfer">Bank Transfer</option>
                <option value="cash">Cash</option>
                <option value="card">Card</option>
                <option value="other">Other</option>
            </select>
            <button
                type="submit"
                :disabled="store.saving"
                class="bg-indigo-600 text-white rounded-lg px-3 py-2 text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
            >
                Save
            </button>
        </form>

        <div v-if="invoice.payments?.length" class="divide-y divide-gray-100">
            <div
                v-for="payment in invoice.payments"
                :key="payment.id"
                class="flex items-center justify-between py-2.5 text-sm"
            >
                <div>
                    <span class="text-gray-900 font-medium"
                        >${{ formatMoney(payment.amount) }}</span
                    >
                    <span class="text-gray-400 mx-2">·</span>
                    <span class="text-gray-500 capitalize">{{
                        payment.method.replace("_", " ")
                    }}</span>
                    <span class="text-gray-400 mx-2">·</span>
                    <span class="text-gray-500">{{
                        formatDate(payment.paid_at)
                    }}</span>
                </div>
                <button
                    class="text-gray-400 hover:text-red-600 text-xs"
                    @click="handleDelete(payment.id)"
                >
                    Remove
                </button>
            </div>
        </div>
        <p v-else class="text-sm text-gray-400 py-2">
            No payments recorded yet.
        </p>
    </div>
</template>

<script setup>
import { reactive, ref } from "vue";
import { useInvoicesStore } from "@/stores/invoices";

const props = defineProps({
    invoice: { type: Object, required: true },
});

const emit = defineEmits(["recorded", "deleted"]);

const store = useInvoicesStore();
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
    await store.recordPayment(props.invoice.id, form);
    form.amount = "";
    showForm.value = false;
    emit("recorded");
}

async function handleDelete(paymentId) {
    if (!confirm("Remove this payment record?")) return;
    await store.deletePayment(props.invoice.id, paymentId);
    emit("deleted");
}
</script>
