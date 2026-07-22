<template>
  <AppLayout>
    <div class="max-w-6xl mx-auto py-6 px-2 sm:px-4">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Proposals Hub</h1>
          <p class="text-xs sm:text-sm text-slate-400 mt-1">Manage, edit, and convert AI-generated proposals for your clients.</p>
        </div>
        <router-link
          :to="{ name: 'proposals.new' }"
          class="inline-flex items-center gap-2 bg-linear-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20 self-start sm:self-auto"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
          <span>New Proposal</span>
        </router-link>
      </div>

      <!-- Search & Filters Bar -->
      <div v-if="store.proposals.length > 0" class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/80 border border-slate-800/80 p-3.5 sm:p-4 rounded-2xl backdrop-blur-xl">
        <!-- Search Input -->
        <div class="relative flex-1 max-w-md">
          <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search proposals by title or client..."
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

      <!-- Empty State (No Proposals) -->
      <div v-else-if="store.proposals.length === 0" class="text-center py-16 bg-slate-900/80 rounded-2xl border border-dashed border-slate-800 backdrop-blur-xl p-8">
        <div class="w-16 h-16 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center text-2xl mx-auto mb-4 ring-1 ring-indigo-500/20">
          📄
        </div>
        <h3 class="text-lg font-bold text-slate-200 mb-1">No proposals found</h3>
        <p class="text-slate-400 text-sm mb-6 max-w-sm mx-auto">Generate professional AI-powered proposals in minutes for your clients.</p>
        <router-link :to="{ name: 'proposals.new' }" class="inline-flex items-center gap-2 text-indigo-400 hover:text-indigo-300 font-semibold text-sm">
          <span>Create First Proposal</span>
          <span>→</span>
        </router-link>
      </div>

      <!-- Empty Filtered Results State -->
      <div
        v-else-if="filteredProposals.length === 0"
        class="text-center py-12 bg-slate-900/80 rounded-2xl border border-slate-800/80 backdrop-blur-xl p-6"
      >
        <p class="text-slate-400 text-sm mb-3">No proposals matching your filter criteria.</p>
        <button
          @click="clearFilters"
          class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 underline"
        >
          Clear filters
        </button>
      </div>

      <!-- Proposal cards -->
      <div v-else class="grid gap-4">
        <div
          v-for="proposal in filteredProposals"
          :key="proposal.id"
          class="bg-slate-900/80 border border-slate-800/80 rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 backdrop-blur-xl hover:border-slate-700/80 transition-all duration-200 hover:shadow-xl hover:shadow-indigo-500/5 group"
        >
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2.5 mb-1.5">
              <h3 class="font-bold text-slate-100 group-hover:text-indigo-300 transition-colors truncate text-base">{{ proposal.title }}</h3>
              <select
                :value="proposal.status"
                @change="handleStatusChange(proposal.id, $event.target.value)"
                class="text-[11px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider shrink-0 ring-1 cursor-pointer focus:outline-none transition-colors"
                :class="statusBadgeClass(proposal.status)"
              >
                <option value="draft" class="bg-slate-900 text-slate-300">Draft</option>
                <option value="sent" class="bg-slate-900 text-blue-400">Sent</option>
                <option value="accepted" class="bg-slate-900 text-emerald-400">Accepted</option>
                <option value="rejected" class="bg-slate-900 text-rose-400">Rejected</option>
              </select>
              <span
                v-if="proposal.generation_step !== 'completed'"
                class="text-[11px] font-semibold px-2.5 py-0.5 rounded-full bg-amber-500/10 text-amber-400 ring-1 ring-amber-500/30 shrink-0"
              >
                In Progress ({{ proposal.generation_step }})
              </span>
            </div>
            <p class="text-xs font-medium text-slate-400 flex items-center gap-2">
              <span class="text-slate-300">{{ proposal.client?.name || 'Unassigned Client' }}</span>
              <span v-if="proposal.total_amount > 0" class="text-slate-500">·</span>
              <span v-if="proposal.total_amount > 0" class="text-emerald-400 font-bold">{{ formatCurrency(proposal.total_amount) }}</span>
            </p>
          </div>

          <div class="flex items-center gap-3 shrink-0">
            <router-link
              :to="{ name: 'proposals.wizard', params: { id: proposal.id } }"
              class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white font-semibold text-xs transition-colors ring-1 ring-slate-700"
            >
              {{ proposal.generation_step === 'completed' ? 'View Details' : 'Continue Wizard' }}
            </router-link>
            <button
              v-if="proposal.generation_step === 'completed'"
              @click="store.downloadPdf(proposal.id)"
              class="p-2 rounded-xl text-slate-400 hover:text-indigo-400 hover:bg-indigo-500/10 transition-colors"
              title="Download PDF"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </button>
            <button @click="confirmDelete(proposal)" class="p-2 rounded-xl text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors" title="Delete Proposal">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useProposalsStore } from '@/stores/proposals'
import { useToast } from '@/composables/useToast'

const store = useProposalsStore()
const toast = useToast()

const searchQuery = ref('')
const statusFilter = ref('all')

const statusOptions = [
  { label: 'All', value: 'all' },
  { label: 'Draft', value: 'draft' },
  { label: 'Sent', value: 'sent' },
  { label: 'Accepted', value: 'accepted' },
  { label: 'Rejected', value: 'rejected' },
]

const filteredProposals = computed(() => {
  return store.proposals.filter(proposal => {
    const q = searchQuery.value.trim().toLowerCase()
    const matchesSearch = !q ||
      proposal.title?.toLowerCase().includes(q) ||
      proposal.client?.name?.toLowerCase().includes(q)
    
    const matchesStatus = statusFilter.value === 'all' || proposal.status === statusFilter.value
    return matchesSearch && matchesStatus
  })
})

function getStatusCount(statusValue) {
  if (statusValue === 'all') return store.proposals.length
  return store.proposals.filter(p => p.status === statusValue).length
}

function clearFilters() {
  searchQuery.value = ''
  statusFilter.value = 'all'
}

onMounted(() => {
  store.fetchProposals()
})

function statusBadgeClass(status) {
  const map = {
    draft: 'bg-slate-800 text-slate-400 ring-slate-700',
    sent: 'bg-blue-500/10 text-blue-400 ring-blue-500/30',
    accepted: 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30',
    rejected: 'bg-rose-500/10 text-rose-400 ring-rose-500/30',
  }
  return map[status] || 'bg-slate-800 text-slate-400 ring-slate-700'
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount)
}

async function handleStatusChange(proposalId, newStatus) {
  try {
    await store.updateProposal(proposalId, { status: newStatus })
    toast.success(`Status updated to ${newStatus.toUpperCase()}`)
  } catch (err) {
    toast.error(store.error || 'Failed to update status.')
  }
}

async function confirmDelete(proposal) {
  if (!confirm(`"${proposal.title}" delete karna hai? Ye action undo nahi ho sakta.`)) return
  try {
    await store.deleteProposal(proposal.id)
    toast.success(`"${proposal.title}" deleted.`)
  } catch (err) {
    toast.error(store.error || 'Failed to delete proposal.')
  }
}
</script>
