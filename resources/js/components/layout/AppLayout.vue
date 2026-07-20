<script setup>
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const router = useRouter();

async function handleLogout() {
    await authStore.logout();
    router.push({ name: "login" });
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 shrink-0">
            <div class="p-6">
                <h1 class="text-xl font-bold text-indigo-600">Propelio</h1>
            </div>
            <nav class="px-4 space-y-1">
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"
                    active-class="bg-indigo-50 text-indigo-600 font-medium"
                >
                    Dashboard
                </router-link>
                <router-link
                    :to="{ name: 'clients' }"
                    class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"
                    active-class="bg-indigo-50 text-indigo-600 font-medium"
                >
                    Clients
                </router-link>
                <router-link
                    :to="{ name: 'proposals.list' }"
                    class="block px-4 py-2 rounded-lg text-gray-700 hover:bg-indigo-50 hover:text-indigo-600"
                    active-class="bg-indigo-50 text-indigo-600 font-medium"
                >
                    📋 Proposals
                </router-link>
                <router-link
                    to="/invoices"
                    active-class="bg-indigo-50 text-indigo-600"
                    class="flex items-center gap-3 px-4 py-2.5 rounded-lg text-gray-600 hover:bg-gray-50"
                >
                    <!-- icon -->
                    <span>Invoices</span>
                </router-link>
            </nav>
        </aside>

        <!-- Main content -->
        <div class="flex-1 flex flex-col">
            <header
                class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center"
            >
                <div></div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600">{{
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

            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
