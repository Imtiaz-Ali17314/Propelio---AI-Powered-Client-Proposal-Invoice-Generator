<!-- resources/js/components/proposals/BriefStep.vue -->
<template>
    <div>
        <h2 class="text-xl sm:text-2xl font-bold text-white mb-1">
            Start a New Proposal
        </h2>
        <p class="text-sm text-slate-400 mb-8">
            Write down the client's brief or requirements. Propelio AI will analyze it to generate the scope, timeline, and cost breakdown.
        </p>

        <form @submit.prevent="handleSubmit" class="space-y-6">
            <!-- Client: prefilled badge -->
            <div v-if="prefilledClient">
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Selected Client</label>
                <div
                    class="flex items-center justify-between bg-indigo-500/10 border border-indigo-500/30 rounded-xl px-4 py-3"
                >
                    <span class="text-sm font-semibold text-indigo-300">{{ prefilledClient.name }}</span>
                    <span class="text-xs text-indigo-400 font-medium">({{ prefilledClient.email }})</span>
                </div>
            </div>

            <!-- Fallback dropdown -->
            <div v-else>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Select Client</label>
                <select
                    v-model="form.client_id"
                    required
                    class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                >
                    <option value="" disabled class="bg-slate-900 text-slate-400">Choose a client...</option>
                    <option
                        v-for="client in clientsStore.clients"
                        :key="client.id"
                        :value="client.id"
                        class="bg-slate-900 text-slate-100"
                    >
                        {{ client.name }} ({{ client.email }})
                    </option>
                </select>
                <p
                    v-if="clientsStore.clients.length === 0"
                    class="text-xs text-amber-400 mt-2 flex items-center gap-1"
                >
                    ⚠️ No clients found. Please add a client from the Clients page first.
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Proposal Title</label>
                <input
                    v-model="form.title"
                    type="text"
                    required
                    placeholder="e.g. E-commerce Website Redesign"
                    class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                />
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Client Brief</label>
                <textarea
                    v-model="form.brief"
                    required
                    minlength="20"
                    rows="6"
                    placeholder="Describe project goals, features requested by client, tech stack, or any special requirements... (min 20 characters)"
                    class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none"
                ></textarea>
                <p class="text-xs text-slate-500 mt-2 text-right">
                    {{ form.brief.length }} characters
                </p>
            </div>

            <button
                type="submit"
                :disabled="submitting || !form.brief || form.brief.length < 20"
                class="w-full bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:from-slate-800 disabled:to-slate-800 disabled:text-slate-600 disabled:cursor-not-allowed text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-200 active:scale-[0.99] ring-1 ring-white/20"
            >
                {{ submitting ? "Creating..." : "Continue to AI Scope Generation →" }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from "vue";
import { useRoute } from "vue-router";
import { useProposalsStore } from "@/stores/proposals";
import { useClientsStore } from "@/stores/clients";
import { useToast } from "@/composables/useToast";

const emit = defineEmits(["created"]);

const route = useRoute();
const store = useProposalsStore();
const clientsStore = useClientsStore();
const toast = useToast();

const submitting = ref(false);

const form = reactive({
    client_id: route.query.client_id || "",
    title: "",
    brief: "",
});

const prefilledClient = computed(() => {
    if (!route.query.client_id) return null;
    return clientsStore.clients.find(
        (c) => c.id === parseInt(route.query.client_id),
    );
});

onMounted(() => {
    if (clientsStore.clients.length === 0) {
        clientsStore.fetchClients();
    }
});

async function handleSubmit() {
    submitting.value = true;
    try {
        const proposal = await store.createProposal({
            client_id: form.client_id,
            title: form.title,
            brief: form.brief,
        });
        emit("created", proposal);
    } catch (err) {
        toast.error(store.error || "Failed to create proposal. Please try again.");
    } finally {
        submitting.value = false;
    }
}
</script>