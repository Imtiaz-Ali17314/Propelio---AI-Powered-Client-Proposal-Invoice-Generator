<template>
    <div class="bg-slate-900/80 rounded-2xl border border-slate-800/80 p-6 backdrop-blur-xl h-full flex flex-col min-h-87.5 max-h-92.5">
        <div class="flex items-center justify-between mb-4 shrink-0">
            <div>
                <h3 class="text-base font-bold text-slate-100">
                    Revenue Trend
                </h3>
                <p class="text-xs text-slate-400">Monthly cash collected over the last 6 months</p>
            </div>
            <div class="px-2.5 py-1 rounded-lg bg-indigo-500/10 text-indigo-400 text-xs font-semibold ring-1 ring-indigo-500/20">
                Cash Flow
            </div>
        </div>
        <div class="flex-1 min-h-0 relative">
            <canvas ref="canvasEl"></canvas>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from "vue";
import {
    Chart,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
} from "chart.js";

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

const props = defineProps({
    data: {
        type: Array,
        default: () => [],
    },
});

const canvasEl = ref(null);
let chartInstance = null;

function currency(value) {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
        maximumFractionDigits: 0,
    }).format(value);
}

function renderChart() {
    if (!canvasEl.value) return;

    const labels = props.data.map((m) => m.label);
    const totals = props.data.map((m) => m.total);

    if (chartInstance) {
        chartInstance.data.labels = labels;
        chartInstance.data.datasets[0].data = totals;
        chartInstance.update();
        return;
    }

    const ctx = canvasEl.value.getContext("2d");
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, "rgba(99, 102, 241, 0.85)");
    gradient.addColorStop(1, "rgba(139, 92, 246, 0.3)");

    chartInstance = new Chart(canvasEl.value, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: "Revenue",
                    data: totals,
                    backgroundColor: gradient,
                    borderColor: "rgba(129, 140, 248, 0.8)",
                    borderWidth: 1,
                    borderRadius: 8,
                    maxBarThickness: 42,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "rgba(15, 23, 42, 0.95)",
                    titleColor: "#f8fafc",
                    bodyColor: "#818cf8",
                    borderColor: "rgba(255, 255, 255, 0.1)",
                    borderWidth: 1,
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: (ctx) => currency(ctx.parsed.y),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: "#94a3b8",
                        font: { family: "Plus Jakarta Sans", size: 11 },
                        callback: (value) => currency(value),
                    },
                    grid: { color: "rgba(51, 65, 85, 0.4)" },
                },
                x: {
                    ticks: {
                        color: "#94a3b8",
                        font: { family: "Plus Jakarta Sans", size: 11 },
                    },
                    grid: { display: false },
                },
            },
        },
    });
}

onMounted(renderChart);

watch(
    () => props.data,
    () => renderChart(),
    { deep: true },
);

onBeforeUnmount(() => {
    chartInstance?.destroy();
});
</script>

