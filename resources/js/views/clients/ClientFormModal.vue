<script setup>
import { ref, watch } from "vue";
import BaseModal from "@/components/ui/BaseModal.vue";
import { useClientsStore } from "@/stores/clients";
import { useToast } from "@/composables/useToast";

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    client: { type: Object, default: null }, // null = create mode, object = edit mode
});

const emit = defineEmits(["update:modelValue", "saved"]);

const clientsStore = useClientsStore();
const toast = useToast();

const form = ref({
    name: "",
    company_name: "",
    email: "",
    phone: "",
    notes: "",
});

const errors = ref({});
const isSaving = ref(false);

// Reset / prefill form whenever the modal opens
watch(
    () => props.modelValue,
    (isOpen) => {
        if (isOpen) {
            errors.value = {};
            form.value = props.client
                ? { ...props.client }
                : {
                      name: "",
                      company_name: "",
                      email: "",
                      phone: "",
                      notes: "",
                  };
        }
    },
);

async function handleSubmit() {
    isSaving.value = true;
    errors.value = {};
    try {
        if (props.client) {
            await clientsStore.updateClient(props.client.id, form.value);
            toast.success("Client updated successfully.");
        } else {
            await clientsStore.createClient(form.value);
            toast.success("Client added successfully.");
        }
        emit("saved");
        emit("update:modelValue", false);
    } catch (err) {
        errors.value = err.response?.data?.errors ?? {};
        toast.error(
            err.response?.data?.message ||
                "Failed to save client. Please check the form and try again.",
        );
    } finally {
        isSaving.value = false;
    }
}
</script>

<template>
    <BaseModal
        :model-value="modelValue"
        :title="client ? 'Edit Client Profile' : 'Add New Client'"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5"
                    >Client Name <span class="text-indigo-400">*</span>
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    placeholder="e.g. Acme Corporation or John Doe"
                    required
                    class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200"
                />
                <p v-if="errors.name" class="text-rose-400 text-xs mt-1 font-medium">
                    {{ errors.name[0] }}
                </p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5"
                    >Company / Business Name</label
                >
                <input
                    v-model="form.company_name"
                    type="text"
                    placeholder="e.g. Acme Tech Solutions LLC"
                    class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5"
                        >Email Address</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        placeholder="client@company.com"
                        class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200"
                    />
                    <p v-if="errors.email" class="text-rose-400 text-xs mt-1 font-medium">
                        {{ errors.email[0] }}
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5"
                        >Phone Number</label
                    >
                    <input
                        v-model="form.phone"
                        type="text"
                        placeholder="+1 (555) 000-0000"
                        class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200"
                    />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5"
                    >Internal Notes <span class="text-[10px] text-slate-500 font-normal lowercase">(optional)</span></label
                >
                <textarea
                    v-model="form.notes"
                    rows="3"
                    placeholder="Add client specifics, preferred communication channels, billing terms..."
                    class="w-full bg-slate-950/80 border border-slate-700/60 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition-all duration-200 resize-none"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800/80 mt-2">
                <button
                    type="button"
                    @click="$emit('update:modelValue', false)"
                    class="px-4 py-2.5 rounded-xl text-sm font-medium text-slate-400 hover:text-slate-200 hover:bg-slate-800/60 transition-all duration-200"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="isSaving"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold text-sm shadow-lg shadow-indigo-600/25 transition-all duration-200 hover:scale-[1.02] active:scale-[0.98] ring-1 ring-white/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                >
                    <svg v-if="isSaving" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                    <span>{{
                        isSaving
                            ? "Saving..."
                            : client
                              ? "Update Client Profile"
                              : "Save Client"
                    }}</span>
                </button>
            </div>
        </form>
    </BaseModal>
</template>