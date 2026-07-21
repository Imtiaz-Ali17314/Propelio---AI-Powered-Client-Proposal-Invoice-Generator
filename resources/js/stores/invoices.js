import { defineStore } from "pinia";
import axios from "axios";

export const useInvoicesStore = defineStore("invoices", {
    state: () => ({
        invoices: [],
        currentInvoice: null,
        loading: false,
        saving: false,
        error: null,
    }),

    actions: {
        async fetchAll() {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await axios.get("/api/invoices");
                this.invoices = data;
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Failed to load invoices.";
            } finally {
                this.loading = false;
            }
        },

        async fetchOne(id) {
            this.loading = true;
            this.error = null;
            try {
                const { data } = await axios.get(`/api/invoices/${id}`);
                this.currentInvoice = data;
                return data;
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Failed to load invoice.";
                throw e;
            } finally {
                this.loading = false;
            }
        },

        async create(payload) {
            this.saving = true;
            this.error = null;
            try {
                const { data } = await axios.post("/api/invoices", payload);
                this.invoices.unshift(data);
                return data;
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Failed to create invoice.";
                throw e;
            } finally {
                this.saving = false;
            }
        },

        async update(id, payload) {
            this.saving = true;
            this.error = null;
            try {
                const { data } = await axios.put(
                    `/api/invoices/${id}`,
                    payload,
                );
                this.currentInvoice = data;
                const idx = this.invoices.findIndex((i) => i.id === id);
                if (idx !== -1) this.invoices[idx] = data;
                return data;
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Failed to update invoice.";
                throw e;
            } finally {
                this.saving = false;
            }
        },

        async remove(id) {
            try {
                await axios.delete(`/api/invoices/${id}`);
                this.invoices = this.invoices.filter((i) => i.id !== id);
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Failed to delete invoice.";
                throw e;
            }
        },

        async convertFromProposal(proposalId) {
            this.saving = true;
            this.error = null;
            try {
                const { data } = await axios.post(
                    `/api/proposals/${proposalId}/convert-to-invoice`,
                );
                this.invoices.unshift(data);
                return data;
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Failed to convert proposal.";
                throw e;
            } finally {
                this.saving = false;
            }
        },

        downloadPdf(id) {
            window.open(`/api/invoices/${id}/pdf`, "_blank");
        },

        async recordPayment(invoiceId, payload) {
            this.saving = true;
            this.error = null;
            try {
                const { data } = await axios.post(
                    `/api/invoices/${invoiceId}/payments`,
                    payload,
                );
                this.currentInvoice = data;
                const idx = this.invoices.findIndex((i) => i.id === invoiceId);
                if (idx !== -1) this.invoices[idx] = data;
                return data;
            } catch (e) {
                this.error =
                    e.response?.data?.errors?.amount?.[0] ||
                    e.response?.data?.message ||
                    "Failed to record payment.";
                throw e;
            } finally {
                this.saving = false;
            }
        },

        async deletePayment(invoiceId, paymentId) {
            try {
                await axios.delete(
                    `/api/invoices/${invoiceId}/payments/${paymentId}`,
                );
                await this.fetchOne(invoiceId);
            } catch (e) {
                this.error =
                    e.response?.data?.message || "Failed to delete payment.";
                throw e;
            }
        },
    },
});