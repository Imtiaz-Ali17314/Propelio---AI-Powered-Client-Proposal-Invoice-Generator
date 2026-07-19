<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Invoices</h1>
        <p class="text-sm text-gray-500 mt-1">Manage and track your client invoices</p>
      </div>
      <router-link
        to="/invoices/create"
        class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700"
      >
        + New Invoice
      </router-link>
    </div>

    <div v-if="store.loading" class="text-center py-16 text-gray-400">
      Loading invoices...
    </div>

    <div v-else-if="store.invoices.length === 0" class="text-center py-16 border border-dashed border-gray-200 rounded-xl">
      <p class="text-gray-500 mb-4">No invoices yet.</p>
      <router-link to="/invoices/create" class="text-indigo-600 font-medium hover:underline">
        Create your first invoice
      </router-link>
    </div>

    <div v-else class="bg-white border border-gray-200 rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
          <tr>
            <th class="text-left px-5 py-3">Invoice #</th>
            <th class="text-left px-5 py-3">Client</th>
            <th class="text-left px-5 py-3">Due Date</th>
            <th class="text-right px-5 py-3">Total</th>
            <th class="text-left px-5 py-3">Status</th>
            <th class="text-right px-5 py-3">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr
            v-for="invoice in store.invoices"
            :key="invoice.id"
            class="hover:bg-gray-50 cursor-pointer"
            @click="$router.push(`/invoices/${invoice.id}`)"
          >
            <td class="px-5 py-3.5 font-medium text-gray-900">{{ invoice.invoice_number }}</td>
            <td class="px-5 py-3.5 text-gray-600">{{ invoice.client?.name || '—' }}</td>
            <td class="px-5 py-3.5 text-gray-600">{{ formatDate(invoice.due_date) }}</td>
            <td class="px-5 py-3.5 text-right text-gray-900">${{ formatMoney(invoice.total) }}</td>
            <td class="px-5 py-3.5">
              <StatusBadge :status="invoice.status" />
            </td>
            <td class="px-5 py-3.5 text-right" @click.stop>
              <button
                class="text-gray-400 hover:text-red-600 text-xs"
                @click="handleDelete(invoice.id)"
              >
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import StatusBadge from '@/components/invoices/StatusBadge.vue'
import { useInvoicesStore } from '@/stores/invoices'

const store = useInvoicesStore()

onMounted(() => {
  store.fetchAll()
})

function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' })
}

function formatMoney(value) {
  return Number(value ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

async function handleDelete(id) {
  if (!confirm('Delete this invoice? This cannot be undone.')) return
  await store.remove(id)
}
</script>