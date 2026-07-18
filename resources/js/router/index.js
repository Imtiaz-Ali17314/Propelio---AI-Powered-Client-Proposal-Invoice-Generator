import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";

const routes = [
    {
        path: "/login",
        name: "login",
        component: () => import("../views/auth/Login.vue"),
        meta: { guestOnly: true },
    },
    {
        path: "/register",
        name: "register",
        component: () => import("../views/auth/Register.vue"),
        meta: { guestOnly: true },
    },
    {
        path: "/",
        name: "dashboard",
        component: () => import("../views/Dashboard.vue"),
        meta: { requiresAuth: true },
    },
    {
        path: "/clients",
        name: "clients",
        component: () => import("../views/clients/ClientList.vue"),
        meta: { requiresAuth: true },
    },
    // Phase 3+: /proposals, /invoices routes will go here
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    // Only fetch the user once per app load, not on every navigation
    if (authStore.user === null && !authStore._checkedAuth) {
        await authStore.fetchUser();
        authStore._checkedAuth = true;
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return { name: "login" };
    }

    if (to.meta.guestOnly && authStore.isAuthenticated) {
        return { name: "dashboard" };
    }
});

export default router;
