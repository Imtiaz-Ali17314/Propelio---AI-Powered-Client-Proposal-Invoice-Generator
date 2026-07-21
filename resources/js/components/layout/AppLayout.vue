<script setup>
import { ref, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import { useAuthStore } from "@/stores/auth";

const authStore = useAuthStore();
const router = useRouter();
const route = useRoute();

const sidebarOpen = ref(false);

function closeSidebar() {
    sidebarOpen.value = false;
}

async function handleLogout() {
    await authStore.logout();
    router.push({ name: "login" });
}

const userInitials = computed(() => {
    const name = authStore.user?.name || "U";
    return name.split(" ").map(n => n[0]).join("").toUpperCase().slice(0, 2);
});

const isDashboardActive = computed(() => route.name === 'dashboard');
const isClientsActive = computed(() => route.name === 'clients' || route.path.startsWith('/clients'));
const isProposalsActive = computed(() => route.name?.toString().startsWith('proposals') || route.path.startsWith('/proposals'));
const isInvoicesActive = computed(() => route.name?.toString().startsWith('invoices') || route.path.startsWith('/invoices'));
</script>

<template>
    <div class="h-screen w-screen overflow-hidden bg-slate-950 text-slate-100 flex font-sans selection:bg-indigo-500 selection:text-white">
        <!-- Mobile backdrop overlay -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-40 lg:hidden transition-opacity"
            @click="closeSidebar"
        ></div>

        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900/95 border-r border-slate-800/80 shrink-0 transform transition-transform duration-300 ease-out lg:relative lg:translate-x-0 flex flex-col backdrop-blur-xl h-full"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <!-- Brand Logo -->
            <div class="p-6 flex items-center justify-between border-b border-slate-800/60 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-linear-to-tr from-indigo-600 via-violet-600 to-purple-500 flex items-center justify-center text-white font-extrabold text-xl shadow-lg shadow-indigo-500/30 ring-1 ring-white/20">
                        P
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-lg font-bold tracking-tight bg-linear-to-r from-white via-slate-100 to-slate-300 bg-clip-text text-transparent">Propelio</span>
                            <span class="px-1.5 py-0.5 text-[10px] font-bold tracking-wider uppercase bg-indigo-500/20 text-indigo-400 rounded ring-1 ring-indigo-500/30">AI</span>
                        </div>
                        <p class="text-[11px] text-slate-400 font-medium">Proposal & Invoice Engine</p>
                    </div>
                </div>
                <button
                    type="button"
                    class="lg:hidden text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800/60"
                    @click="closeSidebar"
                >
                    ✕
                </button>
            </div>

            <!-- Quick Action CTA -->
            <div class="px-4 pt-5 pb-2 shrink-0">
                <router-link
                    :to="{ name: 'proposals.new' }"
                    class="flex items-center justify-center gap-2 w-full py-2.5 px-4 rounded-xl bg-linear-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20"
                    @click="closeSidebar"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Proposal</span>
                </router-link>
            </div>

            <!-- Navigation Links -->
            <nav class="p-4 space-y-1.5 flex-1 overflow-y-auto no-scrollbar">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400 px-3 pb-2 pt-1">Navigation</div>
                
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-all duration-200 group"
                    :class="{ '!bg-indigo-600/15 !text-indigo-400 font-semibold ring-1 ring-indigo-500/30 shadow-sm': isDashboardActive }"
                    @click="closeSidebar"
                >
                    <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    <span>Dashboard</span>
                </router-link>

                <router-link
                    :to="{ name: 'clients' }"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-all duration-200 group"
                    :class="{ '!bg-indigo-600/15 !text-indigo-400 font-semibold ring-1 ring-indigo-500/30 shadow-sm': isClientsActive }"
                    @click="closeSidebar"
                >
                    <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Clients</span>
                </router-link>

                <router-link
                    :to="{ name: 'proposals.list' }"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-all duration-200 group"
                    :class="{ '!bg-indigo-600/15 !text-indigo-400 font-semibold ring-1 ring-indigo-500/30 shadow-sm': isProposalsActive }"
                    @click="closeSidebar"
                >
                    <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Proposals</span>
                </router-link>

                <router-link
                    :to="{ name: 'invoices.index' }"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-all duration-200 group"
                    :class="{ '!bg-indigo-600/15 !text-indigo-400 font-semibold ring-1 ring-indigo-500/30 shadow-sm': isInvoicesActive }"
                    @click="closeSidebar"
                >
                    <svg class="w-5 h-5 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 14l2 2 4-4m5 5.618V4a2 2 0 00-2-2H6a2 2 0 00-2 2v15.618a1 1 0 001.447.894L9 19l3.553 1.512a1 1 0 00.894 0L17 19l3.553 1.512A1 1 0 0022 19.618z"/></svg>
                    <span>Invoices</span>
                </router-link>
            </nav>

            <!-- Bottom User Profile Footer -->
            <div class="p-4 border-t border-slate-800/60 bg-slate-900/60 shrink-0">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <div class="w-9 h-9 rounded-lg bg-indigo-500/20 text-indigo-300 font-bold text-xs flex items-center justify-center ring-1 ring-indigo-500/30 shrink-0">
                            {{ userInitials }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-semibold text-slate-200 truncate">{{ authStore.user?.name || "User Account" }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ authStore.user?.email || "user@agency.com" }}</p>
                        </div>
                    </div>
                    <button
                        @click="handleLogout"
                        class="p-2 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors shrink-0"
                        title="Logout"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main content area -->
        <div class="flex-1 flex flex-col min-w-0 bg-slate-950 h-full overflow-hidden">
            <!-- Top bar -->
            <header class="h-16 bg-slate-900/80 border-b border-slate-800/80 px-4 sm:px-6 flex justify-between items-center backdrop-blur-xl shrink-0 z-30">
                <div class="flex items-center gap-3">
                    <button
                        type="button"
                        class="lg:hidden p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/60 transition-colors"
                        @click="sidebarOpen = true"
                    >
                        <svg class="h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="hidden sm:flex items-center gap-2 text-xs font-medium text-slate-400">
                        <span class="text-slate-400">Agency Hub</span>
                        <span>/</span>
                        <span class="text-slate-200 capitalize font-semibold">{{ route.name?.toString().replace('.', ' / ') || 'Dashboard' }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 text-emerald-400 ring-1 ring-emerald-500/20 text-xs font-medium">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>AI Engine Active</span>
                    </div>
                </div>
            </header>

            <div class="flex-1 overflow-y-auto w-full min-h-0">
                <main class="p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

