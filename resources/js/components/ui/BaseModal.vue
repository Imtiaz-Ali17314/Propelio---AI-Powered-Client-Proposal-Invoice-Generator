<script setup>
defineProps({
    modelValue: { type: Boolean, default: false },
    title: { type: String, default: '' },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="modelValue"
                class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
                @click.self="$emit('update:modelValue', false)"
            >
                <div class="bg-white rounded-xl shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800">{{ title }}</h3>
                        <button
                            @click="$emit('update:modelValue', false)"
                            class="text-gray-400 hover:text-gray-600 text-xl leading-none"
                        >
                            &times;
                        </button>
                    </div>
                    <div class="p-6">
                        <slot />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>