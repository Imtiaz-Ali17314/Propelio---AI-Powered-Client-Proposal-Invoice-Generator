import { defineStore } from "pinia";
import axios from "axios";

export const useProposalsStore = defineStore("proposals", {
    state: () => ({
        proposals: [],
        currentProposal: null,
        loading: false,
        generating: false, // AI call in-progress state (wizard ke liye alag flag)
        error: null,
    }),

    getters: {
        getProposalById: (state) => (id) => {
            return state.proposals.find((p) => p.id === parseInt(id));
        },
    },

    actions: {
        async fetchProposals() {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await axios.get("/api/proposals");
                this.proposals = data;
            } catch (err) {
                this.error =
                    err.response?.data?.message || "Failed to load proposals.";
            } finally {
                this.loading = false;
            }
        },

        async fetchProposal(id) {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await axios.get(`/api/proposals/${id}`);
                this.currentProposal = data;
                return data;
            } catch (err) {
                this.error =
                    err.response?.data?.message || "Failed to load proposal.";
                throw err;
            } finally {
                this.loading = false;
            }
        },

        // Wizard Step 0: brief se proposal create karo
        async createProposal(payload) {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await axios.post("/api/proposals", payload);
                this.proposals.unshift(data);
                this.currentProposal = data;
                return data;
            } catch (err) {
                this.error =
                    err.response?.data?.message || "Failed to create proposal.";
                throw err;
            } finally {
                this.loading = false;
            }
        },

        // Wizard Step 1
        async generateScope(id) {
            this.generating = true;
            this.error = null;
            try {
                const { data } = await axios.post(
                    `/api/proposals/${id}/generate-scope`,
                );
                this.currentProposal = data;
                return data;
            } catch (err) {
                this.error =
                    err.response?.data?.message ||
                    "AI failed to generate scope.";
                throw err;
            } finally {
                this.generating = false;
            }
        },

        // Wizard Step 2
        async generateTimeline(id) {
            this.generating = true;
            this.error = null;
            try {
                const { data } = await axios.post(
                    `/api/proposals/${id}/generate-timeline`,
                );
                this.currentProposal = data;
                return data;
            } catch (err) {
                this.error =
                    err.response?.data?.message ||
                    "AI failed to generate timeline.";
                throw err;
            } finally {
                this.generating = false;
            }
        },

        // Wizard Step 3
        async generateCost(id) {
            this.generating = true;
            this.error = null;
            try {
                const { data } = await axios.post(
                    `/api/proposals/${id}/generate-cost`,
                );
                this.currentProposal = data;
                return data;
            } catch (err) {
                this.error =
                    err.response?.data?.message ||
                    "AI failed to generate cost breakdown.";
                throw err;
            } finally {
                this.generating = false;
            }
        },

        // User manual edits (scope/timeline/cost/title/status)
        async updateProposal(id, payload) {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await axios.put(
                    `/api/proposals/${id}`,
                    payload,
                );
                this.currentProposal = data;
                const idx = this.proposals.findIndex((p) => p.id === id);
                if (idx !== -1) this.proposals[idx] = data;
                return data;
            } catch (err) {
                this.error =
                    err.response?.data?.message || "Failed to update proposal.";
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async deleteProposal(id) {
            this.loading = true;
            this.error = null;
            try {
                await axios.delete(`/api/proposals/${id}`);
                this.proposals = this.proposals.filter((p) => p.id !== id);
            } catch (err) {
                this.error =
                    err.response?.data?.message || "Failed to delete proposal.";
                throw err;
            } finally {
                this.loading = false;
            }
        },

        downloadPdf(id) {
            window.open(`/api/proposals/${id}/pdf`, "_blank");
        },
    },
});
