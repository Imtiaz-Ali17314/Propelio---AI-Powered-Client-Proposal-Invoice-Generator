import { defineStore } from "pinia";
import api from "@/api/axios";

export const useClientsStore = defineStore("clients", {
    state: () => ({
        clients: [],
        pagination: null,
        isLoading: false,
        error: null,
    }),

    actions: {
        async fetchClients(params = {}) {
            this.isLoading = true;
            try {
                const { data } = await api.get("/api/clients", { params });
                this.clients = data.data;
                this.pagination = {
                    currentPage: data.current_page,
                    lastPage: data.last_page,
                    total: data.total,
                };
            } finally {
                this.isLoading = false;
            }
        },

        async createClient(payload) {
            const { data } = await api.post("/api/clients", payload);
            this.clients.unshift(data.client);
            return data.client;
        },

        async updateClient(id, payload) {
            const { data } = await api.put(`/api/clients/${id}`, payload);
            const index = this.clients.findIndex((c) => c.id === id);
            if (index !== -1) this.clients[index] = data.client;
            return data.client;
        },

        async deleteClient(id) {
            await api.delete(`/api/clients/${id}`);
            this.clients = this.clients.filter((c) => c.id !== id);
        },
    },
});
