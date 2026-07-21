<template>
    <Teleport to="body">
        <div
            class="fixed top-4 right-4 z-100 flex flex-col gap-2 w-[calc(100%-2rem)] max-w-sm"
        >
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="flex items-start gap-3 rounded-lg border shadow-lg px-4 py-3 bg-white"
                    :class="styles[toast.type].border"
                >
                    <span class="text-lg leading-none mt-0.5 shrink-0">{{
                        styles[toast.type].icon
                    }}</span>
                    <p class="flex-1 text-sm text-gray-700 wrap-break-words">
                        {{ toast.message }}
                    </p>
                    <button
                        type="button"
                        class="shrink-0 text-gray-400 hover:text-gray-600 leading-none"
                        aria-label="Dismiss"
                        @click="dismiss(toast.id)"
                    >
                        ✕
                    </button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<script setup>
import { useToast } from "@/composables/useToast";

const { toasts, dismiss } = useToast();

const styles = {
    success: { icon: "✅", border: "border-emerald-200" },
    error: { icon: "⛔", border: "border-red-200" },
    warning: { icon: "⚠️", border: "border-amber-200" },
    info: { icon: "ℹ️", border: "border-blue-200" },
};
</script>

<style scoped>
/* Slide-in from the right + fade, and collapse smoothly on dismiss */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.25s ease;
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(24px);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(24px);
}
.toast-leave-active {
    position: absolute;
    width: 100%;
}
.toast-move {
    transition: transform 0.25s ease;
}
</style>
