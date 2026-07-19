<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto">
            <h1 class="text-2xl font-semibold text-gray-900 mb-6">
                {{ isEdit ? "Edit Invoice" : "New Invoice" }}
            </h1>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Client + Dates -->
                <div
                    class="bg-white border border-gray-200 rounded-xl p-5 space-y-4"
                >
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Client</label
                            >
                            <select
                                v-model="form.client_id"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            >
                                <option value="" disabled>
                                    Select a client
                                </option>
                                <option
                                    v-for="client in clientsStore.clients"
                                    :key="client.id"
                                    :value="client.id"
                                >
                                    {{ client.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Tax %</label
                            >
                            <input
                                v-model.number="form.tax_percent"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Issue Date</label
                            >
                            <input
                                v-model="form.issued_date"
                                type="date"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            />
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Due Date</label
                            >
                            <input
                                v-model="form.due_date"
                                type="date"
                                required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            />
                        </div>
                    </div>
                </div>

                <!-- Line Items -->
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h2 class="text-sm font-semibold text-gray-900">
                            Line Items
                        </h2>
                        <button
                            type="button"
                            @click="addItem"
                            class="text-sm text-indigo-600 font-medium hover:underline"
                        >
                            + Add Item
                        </button>
                    </div>

                    <div class="space-y-2">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="grid grid-cols-12 gap-2 items-start"
                        >
                            <input
                                v-model="item.description"
                                type="text"
                                placeholder="Description"
                                required
                                class="col-span-6 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            />
                            <input
                                v-model.number="item.quantity"
                                type="number"
                                min="0.01"
                                step="0.01"
                                placeholder="Qty"
                                required
                                class="col-span-2 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            />
                            <input
                                v-model.number="item.rate"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="Rate"
                                required
                                class="col-span-2 border border-gray-300 rounded-lg px-3 py-2 text-sm"
                            />
                            <div
                                class="col-span-1 text-sm text-gray-500 pt-2 text-right"
                            >
                                {{ formatMoney(lineTotal(item)) }}
                            </div>
                            <button
                                type="button"
                                class="col-span-1 text-gray-400 hover:text-red-600 text-sm pt-2"
                                @click="removeItem(index)"
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    <div
                        class="border-t border-gray-100 mt-4 pt-4 flex justify-end"
                    >
                        <div class="w-56 space-y-1 text-sm">
                            <div class="flex justify-between text-gray-500">
                                <span>Subtotal</span>
                                <span>${{ formatMoney(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-gray-500">
                                <span>Tax ({{ form.tax_percent || 0 }}%)</span>
                                <span>${{ formatMoney(taxAmount) }}</span>
                            </div>
                            <div
                                class="flex justify-between font-semibold text-gray-900 pt-1 border-t border-gray-100"
                            >
                                <span>Total</span>
                                <span>${{ formatMoney(total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="bg-white border border-gray-200 rounded-xl p-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Notes (optional)</label
                    >
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                        placeholder="Payment terms, thank you note, etc."
                    ></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <router-link
                        to="/invoices"
                        class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900"
                    >
                        Cancel
                    </router-link>
                    <button
                        type="submit"
                        :disabled="store.saving"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {{
                            store.saving
                                ? "Saving..."
                                : isEdit
                                  ? "Save Changes"
                                  : "Create Invoice"
                        }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { reactive, computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import AppLayout from "@/components/layout/AppLayout.vue";
import { useInvoicesStore } from "@/stores/invoices";
import { useClientsStore } from "@/stores/clients";

const route = useRoute();
const router = useRouter();
const store = useInvoicesStore();
const clientsStore = useClientsStore();

const isEdit = computed(() => !!route.params.id);

const form = reactive({
    client_id: "",
    issued_date: new Date().toISOString().slice(0, 10),
    due_date: "",
    tax_percent: 0,
    notes: "",
    items: [{ description: "", quantity: 1, rate: 0 }],
});

onMounted(async () => {
    if (clientsStore.clients.length === 0) {
        await clientsStore.fetchAll();
    }

    if (isEdit.value) {
        const invoice = await store.fetchOne(route.params.id);
        form.client_id = invoice.client_id;
        form.issued_date = invoice.issued_date?.slice(0, 10) ?? "";
        form.due_date = invoice.due_date?.slice(0, 10) ?? "";
        form.tax_percent =
            invoice.subtotal > 0
                ? Math.round((invoice.tax / invoice.subtotal) * 10000) / 100
                : 0;
        form.notes = invoice.notes ?? "";
        form.items = invoice.items.length
            ? invoice.items.map((i) => ({
                  id: i.id,
                  description: i.description,
                  quantity: Number(i.quantity),
                  rate: Number(i.rate),
              }))
            : [{ description: "", quantity: 1, rate: 0 }];
    }
});

function addItem() {
    form.items.push({ description: "", quantity: 1, rate: 0 });
}

function removeItem(index) {
    if (form.items.length === 1) return;
    form.items.splice(index, 1);
}

function lineTotal(item) {
    return (Number(item.quantity) || 0) * (Number(item.rate) || 0);
}

const subtotal = computed(() =>
    form.items.reduce((sum, item) => sum + lineTotal(item), 0),
);

const taxAmount = computed(
    () =>
        Math.round(subtotal.value * ((form.tax_percent || 0) / 100) * 100) /
        100,
);

const total = computed(() => subtotal.value + taxAmount.value);

function formatMoney(value) {
    return Number(value ?? 0).toLocaleString("en-US", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

async function handleSubmit() {
    const payload = { ...form };

    try {
        if (isEdit.value) {
            await store.update(route.params.id, payload);
            router.push(`/invoices/${route.params.id}`);
        } else {
            const invoice = await store.create(payload);
            router.push(`/invoices/${invoice.id}`);
        }
    } catch (e) {
        // store.error already set; a toast/alert component can read it if you have one
    }
}
</script>
