<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const router = useRouter();

// Mobile sidebar state — sidebar is hidden off-canvas by default on small
// screens and slides in when toggled. On lg+ screens it's always visible
// (see the `lg:translate-x-0` class below), so this only matters on mobile.
const sidebarOpen = ref(false);

function closeSidebar() {
    sidebarOpen.value = false;
}

async function handleLogout() {
    await authStore.logout();
    router.push({ name: "login" });
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Mobile overlay backdrop -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-black/40 z-30 lg:hidden"
            @click="closeSidebar"
        ></div>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 shrink-0 transform transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="p-6 flex items-center justify-between">
                <h1 class="text-xl font-bold text-indigo-600">Propelio</h1>
                <button
                    type="button"
                    class="lg:hidden text-gray-400 hover:text-gray-600"
                    @click="closeSidebar"
                    aria-label="Close menu"
                >
                    ✕
                </button>
            </div>
            <nav class="px-4 space-y-1">
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"
                    active-class="bg-indigo-50 text-indigo-600 font-medium"
                    @click="closeSidebar"
                >
                    Dashboard
                </router-link>
                <router-link
                    :to="{ name: 'clients' }"
                    class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"
                    active-class="bg-indigo-50 text-indigo-600 font-medium"
                    @click="closeSidebar"
                >
                    Clients
                </router-link>
                <router-link
                    :to="{ name: 'proposals.list' }"
                    class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"
                    active-class="bg-indigo-50 text-indigo-600 font-medium"
                    @click="closeSidebar"
                >
                    📋 Proposals
                </router-link>
                <router-link
                    :to="{ name: 'invoices.index' }"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50"
                    active-class="bg-indigo-50 text-indigo-600"
                    @click="closeSidebar"
                >
                    <span>🧾 Invoices</span>
                </router-link>
            </nav>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <header
                class="bg-white border-b border-gray-200 px-4 sm:px-6 py-4 flex justify-between items-center"
            >
                <button
                    type="button"
                    class="lg:hidden text-gray-500 hover:text-gray-700"
                    @click="sidebarOpen = true"
                    aria-label="Open menu"
                >
                    <!-- simple hamburger icon, no external icon lib needed -->
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-6 w-6"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16"
                        />
                    </svg>
                </button>
                <div class="hidden lg:block"></div>
                <div class="flex items-center gap-2 sm:gap-4">
                    <span class="text-sm text-gray-600 hidden sm:inline">{{
                        authStore.user?.name
                    }}</span>
                    <button
                        @click="handleLogout"
                        class="text-sm text-red-600 hover:underline"
                    >
                        Logout
                    </button>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6 overflow-x-hidden">
                <slot />
            </main>
        </div>
    </div>
</template>
