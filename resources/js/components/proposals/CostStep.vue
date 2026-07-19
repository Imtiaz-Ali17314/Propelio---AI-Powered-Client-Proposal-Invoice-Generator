<!-- resources/js/components/proposals/CostStep.vue -->
<template>
    <div>
        <div class="flex items-center justify-between mb-1">
            <h2 class="text-xl font-semibold text-gray-900">Cost Breakdown</h2>
            <button
                v-if="proposal?.cost_breakdown"
                @click="$emit('generate')"
                :disabled="generating"
                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium flex items-center gap-1 disabled:opacity-50"
            >
                <span>↻</span>
                {{ generating ? "Regenerating..." : "Regenerate" }}
            </button>
        </div>
        <p class="text-sm text-gray-500 mb-6">
            Timeline aur scope ke basis pe AI ne ye pricing suggest ki hai —
            amounts adjust kar sakte ho.
        </p>

        <!-- Empty state -->
        <div v-if="!proposal?.cost_breakdown" class="text-center py-12">
            <p class="text-gray-500 mb-4">
                Cost breakdown abhi generate nahi hui.
            </p>
            <button
                @click="$emit('generate')"
                :disabled="generating"
                class="bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 text-white font-medium px-6 py-3 rounded-lg transition-colors inline-flex items-center gap-2"
            >
                <span v-if="generating" class="animate-spin">⏳</span>
                {{
                    generating
                        ? "AI is thinking..."
                        : "✨ Generate Cost Breakdown with AI"
                }}
            </button>
        </div>

        <!-- Editable form -->
        <div v-else class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Line Items</label
                >
                <div
                    v-for="(item, index) in local.items"
                    :key="index"
                    class="flex gap-2 mb-2 items-center"
                >
                    <input
                        v-model="item.label"
                        type="text"
                        placeholder="Item label"
                        class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                    />
                    <div class="relative w-36">
                        <span
                            class="absolute left-3 top-2 text-sm text-gray-400"
                            >{{ currencySymbol }}</span
                        >
                        <input
                            v-model.number="item.amount"
                            type="number"
                            min="0"
                            step="1"
                            class="w-full border border-gray-300 rounded-lg pl-7 pr-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
                        />
                    </div>
                    <button
                        @click="local.items.splice(index, 1)"
                        class="text-red-400 hover:text-red-600 px-2"
                    >
                        ✕
                    </button>
                </div>
                <button
                    @click="local.items.push({ label: '', amount: 0 })"
                    class="text-sm text-indigo-600 hover:text-indigo-700"
                >
                    + Add line item
                </button>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Currency</label
                    >
                    <select
                        v-model="local.currency"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500"
                    >
                        <option value="USD">USD ($)</option>
                        <option value="PKR">PKR (₨)</option>
                        <option value="EUR">EUR (€)</option>
                        <option value="GBP">GBP (£)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Tax %</label
                    >
                    <input
                        v-model.number="local.tax_percent"
                        type="number"
                        min="0"
                        max="100"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500"
                    />
                </div>
            </div>

            <!-- Auto-calculated totals -->
            <div class="bg-gray-50 rounded-lg p-4 space-y-1.5 text-sm">
                <div class="flex justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span
                        >{{ currencySymbol
                        }}{{ subtotal.toLocaleString() }}</span
                    >
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>Tax ({{ local.tax_percent || 0 }}%)</span>
                    <span
                        >{{ currencySymbol
                        }}{{ taxAmount.toLocaleString() }}</span
                    >
                </div>
                <div
                    class="flex justify-between text-gray-900 font-semibold text-base pt-1.5 border-t border-gray-200"
                >
                    <span>Total</span>
                    <span
                        >{{ currencySymbol }}{{ total.toLocaleString() }}</span
                    >
                </div>
            </div>

            <div class="flex justify-between pt-4 border-t border-gray-100">
                <button
                    @click="$emit('back')"
                    class="text-gray-500 hover:text-gray-700 font-medium px-4 py-2.5"
                >
                    ← Back
                </button>
                <button
                    @click="handleNext"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-6 py-2.5 rounded-lg transition-colors"
                >
                    Finish Proposal →
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
