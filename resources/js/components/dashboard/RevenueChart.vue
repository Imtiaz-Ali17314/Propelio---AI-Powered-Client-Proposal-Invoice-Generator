<template>
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">
            Revenue — Last 6 Months
        </h3>
        <div class="h-64">
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

// Chart.js is "tree-shakeable" — har chart type/scale/plugin ko manually
// register karna parta hai, warna canvas blank rehta hai. Ye registration
// sirf ek dafa hone ki zaroorat hai (module load ke waqt).
Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

const props = defineProps({
    data: {
        type: Array,
        default: () => [], // [{ label: 'Jan 2026', total: 1200 }, ...]
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

    chartInstance = new Chart(canvasEl.value, {
        type: "bar",
        data: {
            labels,
            datasets: [
                {
                    label: "Revenue",
                    data: totals,
                    backgroundColor: "rgba(99, 102, 241, 0.8)", // indigo-500
                    borderRadius: 6,
                    maxBarThickness: 48,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => currency(ctx.parsed.y),
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => currency(value),
                    },
                    grid: { color: "#f1f5f9" },
                },
                x: {
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
