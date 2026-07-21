<template>
  <AppLayout>
    <div class="max-w-6xl mx-auto py-6 px-2 sm:px-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Invoices</h1>
          <p class="text-xs sm:text-sm text-slate-400 mt-1">Track client billings, partial payments, and overdue invoices.</p>
        </div>
        <router-link
          to="/invoices/create"
          class="inline-flex items-center gap-2 bg-linear-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20 self-start sm:self-auto"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>New Invoice</span>
        </router-link>
      </div>

      <div v-if="store.loading" class="space-y-4">
        <div v-for="n in 3" :key="n" class="h-16 bg-slate-900/50 border border-slate-800/50 rounded-2xl animate-pulse shimmer-ai"></div>
      </div>

      <div v-else-if="store.invoices.length === 0" class="text-center py-16 bg-slate-900/80 rounded-2xl border border-dashed border-slate-800 backdrop-blur-xl p-8">
        <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-2xl mx-auto mb-4 ring-1 ring-indigo-500/20">
          🧾
        </div>
        <h3 class="text-lg font-bold text-slate-200 mb-1">No invoices found</h3>
        <p class="text-slate-400 text-sm mb-6 max-w-sm mx-auto">Convert an accepted proposal or create a fresh client invoice.</p>
        <router-link to="/invoices/create" class="inline-flex items-center gap-2 text-indigo-400 hover:text-indigo-300 font-semibold text-sm">
          <span>Create First Invoice</span>
          <span>→</span>
        </router-link>
      </div>

      <div v-else class="bg-slate-900/80 border border-slate-800/80 rounded-2xl overflow-hidden backdrop-blur-xl shadow-xl">
        <div class="overflow-x-auto">
          <table class="w-full text-sm min-w-160">
            <thead class="bg-slate-950/60 text-slate-400 text-xs font-bold uppercase tracking-wider border-b border-slate-800/80">
              <tr>
                <th class="text-left px-6 py-4">Invoice #</th>
                <th class="text-left px-6 py-4">Client</th>
                <th class="text-left px-6 py-4">Due Date</th>
                <th class="text-right px-6 py-4">Total</th>
                <th class="text-left px-6 py-4">Status</th>
                <th class="text-right px-6 py-4">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60">
              <tr
                v-for="invoice in store.invoices"
                :key="invoice.id"
                class="hover:bg-slate-800/40 cursor-pointer transition-colors group"
                @click="$router.push(`/invoices/${invoice.id}`)"
              >
                <td class="px-6 py-4 font-bold text-indigo-300 group-hover:text-indigo-200">{{ invoice.invoice_number }}</td>
                <td class="px-6 py-4 text-slate-300 font-medium">{{ invoice.client?.name || '—' }}</td>
                <td class="px-6 py-4 text-slate-400 font-medium">{{ formatDate(invoice.due_date) }}</td>
                <td class="px-6 py-4 text-right font-extrabold text-slate-100">${{ formatMoney(invoice.total) }}</td>
                <td class="px-6 py-4">
                  <StatusBadge :status="invoice.status" />
                </td>
                <td class="px-6 py-4 text-right" @click.stop>
                  <button
                    class="p-2 text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 rounded-xl transition-colors"
                    title="Delete Invoice"
                    @click="handleDelete(invoice.id)"
                  >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import StatusBadge from '@/components/invoices/StatusBadge.vue'
import { useInvoicesStore } from '@/stores/invoices'
import { useToast } from '@/composables/useToast'

const store = useInvoicesStore()
const toast = useToast()

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
  try {
    await store.remove(id)
    toast.success('Invoice deleted.')
  } catch (e) {
    toast.error(store.error || 'Failed to delete invoice.')
  }
}
</script>
