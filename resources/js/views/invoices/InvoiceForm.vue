<template>
    <AppLayout>
        <div class="max-w-3xl mx-auto py-6 px-2 sm:px-4">
            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
                        {{ isEdit ? "Edit Invoice" : "New Invoice" }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        {{ isEdit ? "Update invoice details and line items." : "Create a new client invoice with itemized billing." }}
                    </p>
                </div>
                <router-link
                    :to="{ name: 'invoices.index' }"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition-all duration-200 text-sm font-medium"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back</span>
                </router-link>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-6">
                <!-- Client + Dates Card -->
                <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 space-y-5 backdrop-blur-xl">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="w-8 h-8 rounded-lg bg-indigo-500/15 text-indigo-400 flex items-center justify-center text-sm ring-1 ring-indigo-500/25">🏢</div>
                        <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wider">Client & Dates</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Client</label>
                            <select
                                v-model="form.client_id"
                                required
                                class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200 appearance-none"
                            >
                                <option value="" disabled class="text-slate-500">Select a client</option>
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
                            <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Tax %</label>
                            <input
                                v-model.number="form.tax_percent"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Issue Date</label>
                            <input
                                v-model="form.issued_date"
                                type="date"
                                required
                                class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200 scheme:dark"
                            />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-400 mb-1.5 uppercase tracking-wider">Due Date</label>
                            <input
                                v-model="form.due_date"
                                type="date"
                                required
                                class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200 scheme:dark"
                            />
                        </div>
                    </div>
                </div>

                <!-- Line Items Card -->
                <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 backdrop-blur-xl">
                    <div class="flex items-center justify-between mb-5">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-violet-500/15 text-violet-400 flex items-center justify-center text-sm ring-1 ring-violet-500/25">📝</div>
                            <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wider">Line Items</h2>
                        </div>
                        <button
                            type="button"
                            @click="addItem"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-400 hover:text-indigo-300 px-3 py-1.5 rounded-lg hover:bg-indigo-500/10 transition-all duration-200"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Add Item
                        </button>
                    </div>

                    <!-- Column headers -->
                    <div class="grid grid-cols-12 gap-2 mb-2 px-1 sm:grid">
                        <span class="col-span-6 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Description</span>
                        <span class="col-span-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Qty</span>
                        <span class="col-span-2 text-[10px] font-bold text-slate-500 uppercase tracking-widest">Rate</span>
                        <span class="col-span-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest text-right">Total</span>
                        <span class="col-span-1"></span>
                    </div>

                    <div class="space-y-2.5">
                        <div
                            v-for="(item, index) in form.items"
                            :key="index"
                            class="grid grid-cols-12 gap-2 items-start group"
                        >
                            <input
                                v-model="item.description"
                                type="text"
                                placeholder="Description"
                                required
                                class="col-span-12 sm:col-span-6 bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200"
                            />
                            <input
                                v-model.number="item.quantity"
                                type="number"
                                min="0.01"
                                step="0.01"
                                placeholder="Qty"
                                required
                                class="col-span-4 sm:col-span-2 bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200"
                            />
                            <input
                                v-model.number="item.rate"
                                type="number"
                                min="0"
                                step="0.01"
                                placeholder="Rate"
                                required
                                class="col-span-4 sm:col-span-2 bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200"
                            />
                            <div class="col-span-3 sm:col-span-1 text-sm font-semibold text-slate-300 pt-2.5 text-right tabular-nums">
                                {{ formatMoney(lineTotal(item)) }}
                            </div>
                            <button
                                type="button"
                                class="col-span-1 text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 text-sm pt-2 rounded-lg transition-all duration-200 w-8 h-8 flex items-center justify-center mx-auto"
                                @click="removeItem(index)"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="border-t border-slate-800/60 mt-6 pt-5 flex justify-end">
                        <div class="w-64 bg-slate-950/60 rounded-xl p-4 ring-1 ring-slate-800/60 space-y-2.5">
                            <div class="flex justify-between text-sm text-slate-400">
                                <span>Subtotal</span>
                                <span class="font-medium text-slate-300 tabular-nums">${{ formatMoney(subtotal) }}</span>
                            </div>
                            <div class="flex justify-between text-sm text-slate-400">
                                <span>Tax ({{ form.tax_percent || 0 }}%)</span>
                                <span class="font-medium text-slate-300 tabular-nums">${{ formatMoney(taxAmount) }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-white pt-2.5 border-t border-slate-700/50">
                                <span>Total</span>
                                <span class="text-lg bg-linear-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent tabular-nums">${{ formatMoney(total) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Card -->
                <div class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 sm:p-6 backdrop-blur-xl">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/15 text-emerald-400 flex items-center justify-center text-sm ring-1 ring-emerald-500/25">💬</div>
                        <h2 class="text-sm font-bold text-slate-200 uppercase tracking-wider">Notes</h2>
                        <span class="text-[10px] text-slate-500 font-medium">(optional)</span>
                    </div>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200 resize-none"
                        placeholder="Payment terms, thank you note, etc."
                    ></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pt-2">
                    <router-link
                        :to="{ name: 'invoices.index' }"
                        class="px-5 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition-all duration-200"
                    >
                        Cancel
                    </router-link>
                    <button
                        type="submit"
                        :disabled="store.saving"
                        class="inline-flex items-center gap-2 px-6 py-2.5 bg-linear-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-600/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                    >
                        <svg v-if="store.saving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span>{{
                            store.saving
                                ? "Saving..."
                                : isEdit
                                  ? "Save Changes"
                                  : "Create Invoice"
                        }}</span>
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
import { useToast } from "@/composables/useToast";

const route = useRoute();
const router = useRouter();
const store = useInvoicesStore();
const clientsStore = useClientsStore();
const toast = useToast();

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
        await clientsStore.fetchClients();
    }

    if (isEdit.value) {
        try {
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
        } catch (e) {
            toast.error(store.error || "Failed to load invoice.");
        }
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
            toast.success("Invoice updated successfully.");
            router.push(`/invoices/${route.params.id}`);
        } else {
            const invoice = await store.create(payload);
            toast.success("Invoice created successfully.");
            router.push(`/invoices/${invoice.id}`);
        }
    } catch (e) {
        toast.error(store.error || "Failed to save invoice. Please try again.");
    }
}
</script>
