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
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Clients</h2>
            <button
                @click="openCreateModal"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700"
            >
                + Add Client
            </button>
        </div>

        <input
            v-model="search"
            @input="handleSearchInput"
            type="text"
            placeholder="Search clients..."
            class="w-full max-w-sm mb-4 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
        />

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-left text-sm min-w-180">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Company</th>
                        <th class="px-6 py-3">Email</th>
                        <th class="px-6 py-3">Proposals</th>
                        <th class="px-6 py-3">Invoices</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="clientsStore.isLoading">
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">Loading...</td>
                    </tr>
                    <tr v-else-if="clientsStore.clients.length === 0">
                        <td colspan="6" class="px-6 py-8 text-center text-gray-400">
                            No clients yet. Add your first client to get started.
                        </td>
                    </tr>
                    <tr v-for="client in clientsStore.clients" :key="client.id" class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-800">{{ client.name }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ client.company_name || '—' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ client.email || '—' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ client.proposals_count }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ client.invoices_count }}</td>
                        <td class="px-6 py-4 text-right space-x-3">
                            <button @click="openEditModal(client)" class="text-indigo-600 hover:underline">
                                Edit
                            </button>
                            <button @click="handleDelete(client)" class="text-red-600 hover:underline">
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            </div>
        </div>

        <ClientFormModal v-model="showModal" :client="editingClient" />
    </AppLayout>
</template>