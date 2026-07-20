<script setup>
import { ref, watch } from "vue";
import BaseModal from "@/components/ui/BaseModal.vue";
import { useClientsStore } from "@/stores/clients";

const props = defineProps({
    modelValue: { type: Boolean, default: false },
    client: { type: Object, default: null }, // null = create mode, object = edit mode
});

const emit = defineEmits(["update:modelValue", "saved"]);

const clientsStore = useClientsStore();

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
        } else {
            await clientsStore.createClient(form.value);
        }
        emit("saved");
        emit("update:modelValue", false);
    } catch (err) {
        errors.value = err.response?.data?.errors ?? {};
    } finally {
        isSaving.value = false;
    }
}
</script>

<template>
    <BaseModal
        :model-value="modelValue"
        :title="client ? 'Edit Client' : 'Add New Client'"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Name *
                </label>
                <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <p v-if="errors.name" class="text-red-600 text-xs mt-1">
                    {{ errors.name[0] }}
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Company Name</label
                >
                <input
                    v-model="form.company_name"
                    type="text"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Email</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="errors.email" class="text-red-600 text-xs mt-1">
                        {{ errors.email[0] }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Phone</label
                    >
                    <input
                        v-model="form.phone"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1"
                    >Notes</label
                >
                <textarea
                    v-model="form.notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                ></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <button
                    type="button"
                    @click="$emit('update:modelValue', false)"
                    class="px-4 py-2 rounded-lg text-gray-600 hover:bg-gray-100"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    :disabled="isSaving"
                    class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-50"
                >
                    {{
                        isSaving
                            ? "Saving..."
                            : client
                              ? "Update Client"
                              : "Add Client"
                    }}
                </button>
            </div>
        </form>
    </BaseModal>
</template>
