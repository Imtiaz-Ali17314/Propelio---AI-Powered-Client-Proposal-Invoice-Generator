import { defineStore } from "pinia";
import api from "../api/axios";

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        isLoading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.user,
    },

    actions: {
        /**
         * Must be called before login/register so Laravel sets the
         * XSRF-TOKEN cookie that Sanctum/CSRF protection needs.
         */
        async getCsrfCookie() {
            await api.get("/sanctum/csrf-cookie");
        },

        async register(payload) {
            this.isLoading = true;
            this.error = null;
            try {
                await this.getCsrfCookie();
                const { data } = await api.post("/api/register", payload);
                this.user = data.user;
            } catch (err) {
                this.error = err.response?.data?.errors ?? {
                    message: "Registration failed.",
                };
                throw err;
            } finally {
                this.isLoading = false;
            }
        },

        async login(payload) {
            this.isLoading = true;
            this.error = null;
            try {
                await this.getCsrfCookie();
                const { data } = await api.post("/api/login", payload);
                this.user = data.user;
            } catch (err) {
                this.error = err.response?.data?.errors ?? {
                    message: "Login failed.",
                };
                throw err;
            } finally {
                this.isLoading = false;
            }
        },

        async logout() {
            await api.post("/api/logout");
            this.user = null;
        },

        async fetchUser() {
            try {
                const { data } = await api.get("/api/user");
                this.user = data;
            } catch (err) {
                this.user = null;
            }
        },
    },
});
