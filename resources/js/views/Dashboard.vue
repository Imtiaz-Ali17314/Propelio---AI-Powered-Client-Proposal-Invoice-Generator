<script setup>
import { onMounted, computed } from "vue";
import AppLayout from "@/components/layout/AppLayout.vue";
import StatCard from "@/components/dashboard/StatCard.vue";
import RevenueChart from "@/components/dashboard/RevenueChart.vue";
import RecentActivity from "@/components/dashboard/RecentActivity.vue";
import { useAuthStore } from "@/stores/auth";
import { useDashboardStore } from "@/stores/dashboard";
import { useToast } from "@/composables/useToast";

const authStore = useAuthStore();
const dashboardStore = useDashboardStore();
const toast = useToast();

onMounted(async () => {
    await dashboardStore.fetchDashboard();
    if (dashboardStore.error) {
        toast.error(dashboardStore.error);
    }
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
        <!-- Header Banner -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                    <span>Welcome back, {{ authStore.user?.name }}</span>
                    <span class="animate-bounce">👋</span>
                </h2>
                <p class="text-slate-400 text-sm mt-1">
                    Here is an overview of your agency's proposals, cash flow, and client invoices.
                </p>
            </div>
            <div>
                <router-link
                    :to="{ name: 'proposals.new' }"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-linear-to-r from-indigo-600 via-violet-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-white font-semibold text-sm shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>New Proposal Wizard</span>
                </router-link>
            </div>
        </div>

        <!-- Loading state -->
        <div v-if="dashboardStore.loading && !stats" class="space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="n in 4"
                    :key="n"
                    class="h-32 rounded-2xl bg-slate-900/50 border border-slate-800/50 animate-pulse shimmer-ai"
                ></div>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <div class="lg:col-span-3 h-72 rounded-2xl bg-slate-900/50 border border-slate-800/50 animate-pulse"></div>
                <div class="lg:col-span-2 h-72 rounded-2xl bg-slate-900/50 border border-slate-800/50 animate-pulse"></div>
            </div>
        </div>

        <!-- Error state -->
        <div
            v-else-if="dashboardStore.error"
            class="bg-rose-500/10 border border-rose-500/30 text-rose-400 rounded-2xl p-4 text-sm"
        >
            {{ dashboardStore.error }}
        </div>

        <!-- Dashboard Content -->
        <div v-else-if="stats" class="space-y-8">
            <!-- Stat cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <StatCard
                    label="Total Revenue"
                    :value="currency(stats.total_revenue)"
                    subtext="Collected from payments"
                    icon="💵"
                    icon-bg="bg-emerald-500/20 text-emerald-300 ring-emerald-500/30"
                />
                <StatCard
                    label="Pending Amount"
                    :value="currency(stats.pending_amount)"
                    subtext="Outstanding across invoices"
                    icon="⏳"
                    icon-bg="bg-amber-500/20 text-amber-300 ring-amber-500/30"
                />
                <StatCard
                    label="Proposals"
                    :value="stats.proposals.total"
                    :subtext="proposalsSubtext"
                    icon="📋"
                    icon-bg="bg-indigo-500/20 text-indigo-300 ring-indigo-500/30"
                />
                <StatCard
                    label="Invoices"
                    :value="stats.invoices.total"
                    :subtext="invoicesSubtext"
                    icon="🧾"
                    icon-bg="bg-violet-500/20 text-violet-300 ring-violet-500/30"
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

