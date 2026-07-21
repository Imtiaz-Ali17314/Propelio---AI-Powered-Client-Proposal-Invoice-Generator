<!-- resources/js/components/proposals/BriefStep.vue -->
<template>
    <div>
        <h2 class="text-xl font-semibold text-gray-900 mb-1">
            Start a New Proposal
        </h2>
        <p class="text-sm text-gray-500 mb-6">
            Client ka brief likho — AI is se scope, timeline, aur cost breakdown
            generate karega.
        </p>

        <form @submit.prevent="handleSubmit" class="space-y-5">
            <!-- Client: agar query se mila hai to readonly badge dikhao -->
            <div v-if="prefilledClient">
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Client</label
                >
                <div
                    class="flex items-center gap-2 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2.5"
                >
                    <span class="text-sm font-medium text-indigo-700">{{
                        prefilledClient.name
                    }}</span>
                    <span class="text-xs text-indigo-400"
                        >({{ prefilledClient.email }})</span
                    >
                </div>
            </div>

            <!-- Fallback: dropdown agar client_id query mein nahi mila -->
            <div v-else>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Select Client</label
                >
                <select
                    v-model="form.client_id"
                    required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                    <option value="" disabled>Choose a client...</option>
                    <option
                        v-for="client in clientsStore.clients"
                        :key="client.id"
                        :value="client.id"
                    >
                        {{ client.name }} ({{ client.email }})
                    </option>
                </select>
                <p
                    v-if="clientsStore.clients.length === 0"
                    class="text-xs text-amber-600 mt-1"
                >
                    Koi client nahi mila — pehle Clients page se ek client add
                    karo.
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Proposal Title</label
                >
                <input
                    v-model="form.title"
                    type="text"
                    required
                    placeholder="e.g. E-commerce Website Redesign"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Client Brief</label
                >
                <textarea
                    v-model="form.brief"
                    required
                    minlength="20"
                    rows="6"
                    placeholder="Client ne kya kaha? Project ki requirement, goals, aur koi special instructions likho... (min 20 characters)"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 resize-none"
                ></textarea>
                <p class="text-xs text-gray-400 mt-1">
                    {{ form.brief.length }} characters
                </p>
            </div>

            <button
                type="submit"
                :disabled="submitting || !form.brief || form.brief.length < 20"
                class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-medium py-3 rounded-lg transition-colors"
            >
                {{ submitting ? "Creating..." : "Continue to AI Generation →" }}
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
    // Fallback dropdown ke liye client list load karo (agar already load nahi hui)
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