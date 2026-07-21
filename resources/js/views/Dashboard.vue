<script setup>
import { onMounted, computed } from "vue";
import AppLayout from "@/components/layout/AppLayout.vue";
import StatCard from "@/components/dashboard/StatCard.vue";
import RevenueChart from "@/components/dashboard/RevenueChart.vue";
import RecentActivity from "@/components/dashboard/RecentActivity.vue";
import { useAuthStore } from "@/stores/auth";
import { useDashboardStore } from "@/stores/dashboard";

const authStore = useAuthStore();
const dashboardStore = useDashboardStore();

onMounted(() => {
    dashboardStore.fetchDashboard();
});

function currency(amount) {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(amount || 0);
}

const stats = computed(() => dashboardStore.stats);

const proposalsSubtext = computed(() => {
    const p = stats.value?.proposals;
    if (!p) return "";
    return `${p.accepted} accepted · ${p.sent} sent · ${p.draft} draft`;
});

const invoicesSubtext = computed(() => {
    const i = stats.value?.invoices;
    if (!i) return "";
    return `${i.paid} paid · ${i.unpaid} unpaid · ${i.overdue} overdue`;
});
</script>

<template>
    <AppLayout>
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-1">
                Welcome back, {{ authStore.user?.name }} 👋
            </h2>
            <p class="text-gray-500 text-sm">
                Here's how your agency is doing.
            </p>
        </div>

        <!-- Loading state -->
        <div v-if="dashboardStore.loading && !stats" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="n in 4"
                    :key="n"
                    class="h-28 rounded-xl bg-gray-100 animate-pulse"
                ></div>
            </div>
            <div class="h-64 rounded-xl bg-gray-100 animate-pulse"></div>
        </div>

        <!-- Error state -->
        <div
            v-else-if="dashboardStore.error"
            class="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 text-sm"
        >
            {{ dashboardStore.error }}
        </div>

        <!-- Content -->
        <div v-else-if="stats" class="space-y-6">
            <!-- Stat cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    label="Total Revenue"
                    :value="currency(stats.total_revenue)"
                    subtext="Collected from payments"
                    icon="💰"
                    icon-bg="bg-emerald-50"
                />
                <StatCard
                    label="Pending Amount"
                    :value="currency(stats.pending_amount)"
                    subtext="Outstanding across unpaid invoices"
                    icon="⏳"
                    icon-bg="bg-amber-50"
                />
                <StatCard
                    label="Proposals"
                    :value="stats.proposals.total"
                    :subtext="proposalsSubtext"
                    icon="📋"
                    icon-bg="bg-indigo-50"
                />
                <StatCard
                    label="Invoices"
                    :value="stats.invoices.total"
                    :subtext="invoicesSubtext"
                    icon="🧾"
                    icon-bg="bg-blue-50"
                />
            </div>

            <!-- Chart + Recent activity -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <div class="lg:col-span-3">
                    <RevenueChart :data="dashboardStore.revenueChart" />
                </div>
                <div class="lg:col-span-2">
                    <RecentActivity
                        :proposals="dashboardStore.recentProposals"
                        :invoices="dashboardStore.recentInvoices"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
