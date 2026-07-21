<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '@/components/layout/AppLayout.vue';
import ClientFormModal from './ClientFormModal.vue';
import { useClientsStore } from '@/stores/clients';
import { useToast } from '@/composables/useToast';

const clientsStore = useClientsStore();
const toast = useToast();

const showModal = ref(false);
const editingClient = ref(null);
const search = ref('');

onMounted(() => {
    clientsStore.fetchClients();
});

function openCreateModal() {
    editingClient.value = null;
    showModal.value = true;
}

function openEditModal(client) {
    editingClient.value = client;
    showModal.value = true;
}

async function handleDelete(client) {
    if (!confirm(`Delete client "${client.name}"? This cannot be undone.`)) return;
    try {
        await clientsStore.deleteClient(client.id);
        toast.success(`Client "${client.name}" deleted.`);
    } catch (err) {
        toast.error(
            err.response?.data?.message || 'Failed to delete client.',
        );
    }
}

let searchTimeout = null;
function handleSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        clientsStore.fetchClients({ search: search.value });
    }, 300);
}
</script>

<template>
    <AppLayout>
        <div class="max-w-6xl mx-auto py-6 px-2 sm:px-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Clients Directory</h1>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">Manage client accounts, contact details, and proposal histories.</p>
                </div>
                <button
                    @click="openCreateModal"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20 self-start sm:self-auto"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Client</span>
                </button>
            </div>

            <!-- Search bar -->
            <div class="mb-6 max-w-md">
                <div class="relative">
                    <input
                        v-model="search"
                        @input="handleSearchInput"
                        type="text"
                        placeholder="Search clients by name or company..."
                        class="w-full bg-slate-900/80 border border-slate-800 rounded-xl pl-10 pr-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all backdrop-blur-xl"
                    />
                    <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Loading State -->
            <div v-if="clientsStore.isLoading" class="space-y-4">
                <div v-for="n in 3" :key="n" class="h-20 bg-slate-900/50 border border-slate-800/50 rounded-2xl animate-pulse shimmer-ai"></div>
            </div>

            <!-- Empty State -->
            <div v-else-if="clientsStore.clients.length === 0" class="text-center py-16 bg-slate-900/80 rounded-2xl border border-dashed border-slate-800 backdrop-blur-xl p-8">
                <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-2xl mx-auto mb-4 ring-1 ring-indigo-500/20">
                    👥
                </div>
                <h3 class="text-lg font-bold text-slate-200 mb-1">No clients found</h3>
                <p class="text-slate-400 text-sm mb-6 max-w-sm mx-auto">Create client accounts to associate with proposals and invoices.</p>
                <button @click="openCreateModal" class="inline-flex items-center gap-2 text-indigo-400 hover:text-indigo-300 font-semibold text-sm">
                    <span>Add First Client</span>
                    <span>→</span>
                </button>
            </div>

            <!-- Data Table -->
            <div v-else class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-xl shadow-xl">
                <div class="overflow-x-auto">
                <table class="w-full text-left text-sm min-w-180">
                    <thead class="bg-slate-950/60 text-slate-400 font-bold uppercase text-xs tracking-wider border-b border-slate-800/80">
                        <tr>
                            <th class="px-6 py-4">Client Name</th>
                            <th class="px-6 py-4">Company</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Proposals</th>
                            <th class="px-6 py-4">Invoices</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60">
                        <tr v-for="client in clientsStore.clients" :key="client.id" class="hover:bg-slate-800/40 transition-colors group">
                            <td class="px-6 py-4 font-bold text-slate-200 group-hover:text-indigo-300">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-300 font-bold text-xs flex items-center justify-center ring-1 ring-indigo-500/30 shrink-0">
                                        {{ (client.name || 'C')[0].toUpperCase() }}
                                    </div>
                                    <span>{{ client.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-300 font-medium">{{ client.company_name || '—' }}</td>
                            <td class="px-6 py-4 text-slate-400 font-medium">{{ client.email || '—' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full bg-slate-800 text-indigo-400 font-bold text-xs ring-1 ring-slate-700">
                                    {{ client.proposals_count || 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full bg-slate-800 text-violet-400 font-bold text-xs ring-1 ring-slate-700">
                                    {{ client.invoices_count || 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-1">
                                <button @click="openEditModal(client)" class="p-1.5 text-slate-400 hover:text-indigo-400 hover:bg-indigo-500/10 rounded-lg transition-all duration-200" title="Edit Client">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button @click="handleDelete(client)" class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition-all duration-200" title="Delete Client">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>

            <ClientFormModal v-model="showModal" :client="editingClient" />
        </div>
    </AppLayout>
</template>