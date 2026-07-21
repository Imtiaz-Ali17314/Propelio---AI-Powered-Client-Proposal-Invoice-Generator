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
                class="fixed inset-0 bg-slate-950/80 backdrop-blur-md flex items-center justify-center z-50 p-4 transition-all"
                @click.self="$emit('update:modelValue', false)"
            >
                <div class="bg-slate-900/95 border border-slate-800/90 rounded-2xl shadow-2xl shadow-indigo-950/40 w-full max-w-lg max-h-[90vh] overflow-y-auto text-slate-100 backdrop-blur-xl ring-1 ring-white/10">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800/80 bg-slate-900/50">
                        <h3 class="text-lg font-extrabold tracking-tight text-white flex items-center gap-2">
                            <span>{{ title }}</span>
                        </h3>
                        <button
                            type="button"
                            @click="$emit('update:modelValue', false)"
                            class="w-8 h-8 rounded-xl bg-slate-800/60 hover:bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center transition-colors text-sm font-semibold"
                        >
                            ✕
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