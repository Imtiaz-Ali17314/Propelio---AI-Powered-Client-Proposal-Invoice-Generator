import { defineStore } from "pinia";
import api from "@/api/axios";

export const useDashboardStore = defineStore("dashboard", {
    state: () => ({
        stats: null,
        revenueChart: [],
        recentProposals: [],
        recentInvoices: [],
        loading: false,
        error: null,
    }),

    actions: {
        async fetchDashboard() {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await api.get("/api/dashboard");
                this.stats = data.stats;
                this.revenueChart = data.revenue_chart;
                this.recentProposals = data.recent_proposals;
                this.recentInvoices = data.recent_invoices;
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Failed to load dashboard.";
            } finally {
                this.loading = false;
            }
        },
    },
});
