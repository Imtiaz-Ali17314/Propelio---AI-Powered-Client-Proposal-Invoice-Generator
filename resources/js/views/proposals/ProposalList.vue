<template>
  <AppLayout>
    <div class="max-w-6xl mx-auto py-8 px-4">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Proposals</h1>
          <p class="text-sm text-gray-500 mt-1">AI-generated client proposals — manage aur track karo.</p>
        </div>
        <router-link
          :to="{ name: 'proposals.new' }"
          class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-5 py-2.5 rounded-lg transition-colors flex items-center gap-2"
        >
          <span>✨</span> New Proposal
        </router-link>
      </div>

      <!-- Loading -->
      <div v-if="store.loading && store.proposals.length === 0" class="text-center py-16 text-gray-400">
        Loading proposals...
      </div>

      <!-- Empty state -->
      <div
        v-else-if="!store.loading && store.proposals.length === 0"
        class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-200"
      >
        <p class="text-gray-500 mb-4">Abhi tak koi proposal nahi banayi.</p>
        <router-link :to="{ name: 'proposals.new' }" class="text-indigo-600 hover:text-indigo-700 font-medium">
          Pehli proposal banao →
        </router-link>
      </div>

      <!-- Proposal cards -->
      <div v-else class="grid gap-4">
        <div
          v-for="proposal in store.proposals"
          :key="proposal.id"
          class="bg-white border border-gray-100 rounded-xl p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 hover:shadow-sm transition-shadow"
        >
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-1">
              <h3 class="font-medium text-gray-900 truncate">{{ proposal.title }}</h3>
              <span class="text-xs font-medium px-2.5 py-0.5 rounded-full shrink-0" :class="statusBadgeClass(proposal.status)">
                {{ proposal.status }}
              </span>
              <span
                v-if="proposal.generation_step !== 'completed'"
                class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-600 shrink-0"
              >
                Draft in progress
              </span>
            </div>
            <p class="text-sm text-gray-500">
              {{ proposal.client?.name || 'Unknown client' }}
              <span v-if="proposal.total_amount > 0"> · {{ formatCurrency(proposal.total_amount) }}</span>
            </p>
          </div>

          <div class="flex items-center gap-2 shrink-0 sm:ml-4">
            <router-link
              :to="{ name: 'proposals.wizard', params: { id: proposal.id } }"
              class="text-sm text-indigo-600 hover:text-indigo-700 font-medium px-3 py-1.5"
            >
              {{ proposal.generation_step === 'completed' ? 'View' : 'Continue' }}
            </router-link>
            <button @click="confirmDelete(proposal)" class="text-sm text-red-400 hover:text-red-600 px-3 py-1.5">
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { onMounted } from 'vue'
import AppLayout from '@/components/layout/AppLayout.vue'
import { useProposalsStore } from '@/stores/proposals'
import { useToast } from '@/composables/useToast'

const store = useProposalsStore()
const toast = useToast()

onMounted(() => {
  store.fetchProposals()
})

function statusBadgeClass(status) {
  const map = {
    draft: 'bg-gray-100 text-gray-600',
    sent: 'bg-blue-50 text-blue-600',
    accepted: 'bg-emerald-50 text-emerald-600',
    rejected: 'bg-red-50 text-red-600',
  }
  return map[status] || 'bg-gray-100 text-gray-600'
}

function formatCurrency(amount) {
  return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount)
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