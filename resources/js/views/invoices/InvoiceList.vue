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

      <!-- Search & Filters Bar -->
      <div v-if="store.invoices.length > 0" class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/80 border border-slate-800/80 p-3.5 sm:p-4 rounded-2xl backdrop-blur-xl">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
          <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search invoices by number or client..."
            class="w-full bg-slate-950/80 border border-slate-800 rounded-xl pl-10 pr-9 py-2 text-xs sm:text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 transition-all"
          />
          <button
            v-if="searchQuery"
            @click="searchQuery = ''"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300 text-xs"
          >
            ✕
          </button>
        </div>

        <!-- Status Filter Pills -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 scrollbar-none">
          <button
            v-for="status in statusOptions"
            :key="status.value"
            @click="statusFilter = status.value"
            class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all shrink-0 flex items-center gap-1.5"
            :class="
              statusFilter === status.value
                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-600/30'
                : 'bg-slate-950/60 text-slate-400 hover:bg-slate-800 hover:text-slate-200 border border-slate-800/80'
            "
          >
            <span>{{ status.label }}</span>
            <span
              class="px-1.5 py-0.2 rounded-full text-[10px]"
              :class="statusFilter === status.value ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400'"
            >
              {{ getStatusCount(status.value) }}
            </span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="store.loading" class="space-y-4">
        <div v-for="n in 3" :key="n" class="h-20 bg-slate-900/50 border border-slate-800/50 rounded-2xl animate-pulse shimmer-ai"></div>
      </div>

      <!-- Empty State (No Invoices) -->
      <div v-else-if="store.invoices.length === 0" class="text-center py-16 bg-slate-900/80 rounded-2xl border border-dashed border-slate-800 backdrop-blur-xl p-8">
        <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-2xl mx-auto mb-4 ring-1 ring-indigo-500/20">
          💳
        </div>
        <h3 class="text-lg font-bold text-slate-200 mb-1">No invoices found</h3>
        <p class="text-slate-400 text-sm mb-6 max-w-sm mx-auto">Create and manage client invoices, track partial payments, and handle billing easily.</p>
        <router-link to="/invoices/create" class="inline-flex items-center gap-2 text-indigo-400 hover:text-indigo-300 font-semibold text-sm">
          <span>Create First Invoice</span>
          <span>→</span>
        </router-link>
      </div>

      <!-- Empty Filtered State -->
      <div
        v-else-if="filteredInvoices.length === 0"
        class="text-center py-12 bg-slate-900/80 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6"
      >
        <p class="text-slate-400 text-sm mb-3">No invoices matching your filter criteria.</p>
        <button
          @click="clearFilters"
          class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 underline"
        >
          Clear filters
        </button>
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
                v-for="invoice in filteredInvoices"
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
                  <div class="flex items-center justify-end gap-1">
                    <button
                      v-if="invoice.status === 'cancelled'"
                      class="p-2 text-emerald-400 hover:bg-emerald-500/10 rounded-xl transition-colors"
                      title="Reactivate Invoice"
                      @click="handleToggleCancel(invoice)"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                    <button
                      v-else
                      class="p-2 text-slate-500 hover:text-amber-400 hover:bg-amber-500/10 rounded-xl transition-colors"
                      title="Cancel Invoice"
                      @click="handleToggleCancel(invoice)"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </button>
                    <button
                      class="p-2 text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 rounded-xl transition-colors"
                      title="Delete Invoice"
                      @click="handleDelete(invoice.id)"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>
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
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import StatusBadge from '@/components/invoices/StatusBadge.vue'
import { useInvoicesStore } from '@/stores/invoices'
import { useToast } from '@/composables/useToast'

const store = useInvoicesStore()
const toast = useToast()

const searchQuery = ref('')
const statusFilter = ref('all')

const statusOptions = [
  { label: 'All', value: 'all' },
  { label: 'Unpaid', value: 'unpaid' },
  { label: 'Partially Paid', value: 'partially_paid' },
  { label: 'Paid', value: 'paid' },
  { label: 'Overdue', value: 'overdue' },
  { label: 'Cancelled', value: 'cancelled' },
]

const filteredInvoices = computed(() => {
  return store.invoices.filter(invoice => {
    const q = searchQuery.value.trim().toLowerCase()
    const matchesSearch = !q ||
      invoice.invoice_number?.toLowerCase().includes(q) ||
      invoice.client?.name?.toLowerCase().includes(q)

    const matchesStatus = statusFilter.value === 'all' || invoice.status === statusFilter.value
    return matchesSearch && matchesStatus
  })
})

function getStatusCount(statusValue) {
  if (statusValue === 'all') return store.invoices.length
  return store.invoices.filter(i => i.status === statusValue).length
}

function clearFilters() {
  searchQuery.value = ''
  statusFilter.value = 'all'
}

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

async function handleToggleCancel(invoice) {
  const isCancelling = invoice.status !== 'cancelled'
  const confirmMsg = isCancelling
    ? `Are you sure you want to cancel invoice "${invoice.invoice_number}"?`
    : `Reactivate invoice "${invoice.invoice_number}"?`

  if (!confirm(confirmMsg)) return

  try {
    const res = await store.toggleCancel(invoice.id)
    toast.success(res.message || (isCancelling ? 'Invoice cancelled.' : 'Invoice reactivated.'))
  } catch (e) {
    toast.error(store.error || 'Failed to update invoice status.')
  }
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
