<template>
    <div>
        <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
            <h2 class="text-xl sm:text-2xl font-bold text-white tracking-tight">Cost Breakdown</h2>
            <button
                v-if="proposal?.cost_breakdown"
                @click="$emit('generate')"
                :disabled="generating"
                class="text-xs sm:text-sm text-indigo-400 hover:text-indigo-300 font-semibold flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 ring-1 ring-indigo-500/30 transition-all disabled:opacity-50"
            >
                <span :class="{ 'animate-spin': generating }">↻</span>
                {{ generating ? "Regenerating Pricing..." : "Regenerate AI Cost" }}
            </button>
        </div>
        <p class="text-xs sm:text-sm text-slate-400 mb-6">
            AI calculated pricing breakdown based on scope and timeline — customize line items and tax.
        </p>

        <!-- Empty state -->
        <div v-if="!proposal?.cost_breakdown" class="text-center py-12 bg-slate-950/40 rounded-2xl border border-dashed border-slate-800 p-8">
            <p class="text-slate-400 mb-4 text-sm">Cost breakdown has not been generated yet.</p>
            <button
                @click="$emit('generate')"
                :disabled="generating"
                class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold px-6 py-3 rounded-xl shadow-lg shadow-indigo-600/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20 disabled:opacity-50"
            >
                <span v-if="generating" class="animate-spin">⏳</span>
                <span>{{
                    generating
                        ? "AI is calculating pricing..."
                        : "✨ Generate Cost Breakdown with AI"
                }}</span>
            </button>
        </div>

        <!-- Editable form -->
        <div v-else class="space-y-6">
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3"
                    >Line Items</label
                >
                <div
                    v-for="(item, index) in local.items"
                    :key="index"
                    class="flex flex-col sm:flex-row gap-2.5 mb-2.5 sm:items-center"
                >
                    <input
                        v-model="item.label"
                        type="text"
                        placeholder="Item label (e.g. Design & Frontend Implementation)"
                        class="flex-1 bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                    />
                    <div class="flex gap-2 items-center">
                        <div class="relative flex-1 sm:w-40 sm:flex-none">
                            <span
                                class="absolute left-3.5 top-2.5 text-sm text-slate-400 font-semibold"
                                >{{ currencySymbol }}</span
                            >
                            <input
                                v-model.number="item.amount"
                                type="number"
                                min="0"
                                step="1"
                                class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl pl-8 pr-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 tabular-nums"
                            />
                        </div>
                        <button
                            @click="local.items.splice(index, 1)"
                            class="text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 w-9 h-9 flex items-center justify-center rounded-xl transition-all shrink-0"
                        >
                            ✕
                        </button>
                    </div>
                </div>
                <button
                    @click="local.items.push({ label: '', amount: 0 })"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-400 hover:text-indigo-300 px-3 py-1.5 rounded-lg hover:bg-indigo-500/10 transition-all"
                >
                    + Add Line Item
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2"
                        >Currency</label
                    >
                    <select
                        v-model="local.currency"
                        class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 appearance-none"
                    >
                        <option value="USD">USD ($)</option>
                        <option value="PKR">PKR (₨)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2"
                        >Tax Rate %</label
                    >
                    <input
                        v-model.number="local.tax_percent"
                        type="number"
                        min="0"
                        max="100"
                        class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/50"
                    />
                </div>
            </div>

            <!-- Auto-calculated totals -->
            <div class="bg-slate-950/60 rounded-2xl p-5 ring-1 ring-slate-800/80 space-y-2.5 text-sm">
                <div class="flex justify-between text-slate-400">
                    <span>Subtotal</span>
                    <span class="font-medium text-slate-200 tabular-nums"
                        >{{ currencySymbol
                        }}{{ subtotal.toLocaleString() }}</span
                    >
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Tax ({{ local.tax_percent || 0 }}%)</span>
                    <span class="font-medium text-slate-200 tabular-nums"
                        >{{ currencySymbol
                        }}{{ taxAmount.toLocaleString() }}</span
                    >
                </div>
                <div
                    class="flex justify-between text-white font-extrabold text-lg pt-2.5 border-t border-slate-800/80"
                >
                    <span>Total Cost</span>
                    <span class="bg-gradient-to-r from-indigo-400 to-violet-400 bg-clip-text text-transparent tabular-nums"
                        >{{ currencySymbol }}{{ total.toLocaleString() }}</span
                    >
                </div>
            </div>

            <div class="flex items-center justify-between pt-5 border-t border-slate-800/80">
                <button
                    @click="$emit('back')"
                    class="px-4 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition-all"
                >
                    ← Back
                </button>
                <button
                    @click="handleNext"
                    class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20"
                >
                    <span>Finish Proposal</span>
                    <span>→</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed, watch } from "vue";

const props = defineProps({
    proposal: Object,
    generating: Boolean,
});

const emit = defineEmits(["generate", "next", "back", "update"]);

const local = reactive({
    items: [],
    currency: "USD",
    tax_percent: 0,
});

const currencySymbols = { USD: "$", PKR: "₨", EUR: "€", GBP: "£" };
const currencySymbol = computed(() => currencySymbols[local.currency] || "");

const subtotal = computed(() =>
    local.items.reduce((sum, item) => sum + (Number(item.amount) || 0), 0),
);
const taxAmount = computed(
    () => (subtotal.value * (local.tax_percent || 0)) / 100,
);
const total = computed(() => subtotal.value + taxAmount.value);

watch(
    () => props.proposal?.cost_breakdown,
    (newCost) => {
        if (newCost) {
            local.items = (newCost.items || []).map((i) => ({ ...i }));
            local.currency = newCost.currency || "USD";
            local.tax_percent = newCost.tax_percent || 0;
        }
    },
    { immediate: true, deep: true },
);

async function handleNext() {
    await emit("update", {
        items: local.items.filter((i) => i.label?.trim()),
        currency: local.currency,
        subtotal: subtotal.value,
        tax_percent: local.tax_percent,
        total: total.value,
    });
    emit("next");
}
</script>