<template>
    <Teleport to="body">
        <div
            class="fixed top-5 right-5 z-100 flex flex-col gap-3 w-[calc(100%-2.5rem)] max-w-sm pointer-events-none"
        >
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    class="pointer-events-auto flex items-center gap-3.5 rounded-2xl border p-4 bg-slate-900/95 backdrop-blur-2xl shadow-2xl shadow-slate-950/80 ring-1 ring-white/10 transition-all duration-300 group hover:scale-[1.01]"
                    :class="styles[toast.type]?.container || styles.info.container"
                >
                    <!-- Icon badge -->
                    <div
                        class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 ring-1"
                        :class="styles[toast.type]?.badge || styles.info.badge"
                    >
                        <svg
                            v-if="toast.type === 'success'"
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M5 13l4 4L19 7"
                            />
                        </svg>
                        <svg
                            v-else-if="toast.type === 'error'"
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                        <svg
                            v-else-if="toast.type === 'warning'"
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                        <svg
                            v-else
                            class="w-4 h-4"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2.5"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                            />
                        </svg>
                    </div>

                    <!-- Message -->
                    <p class="flex-1 text-xs font-semibold text-slate-100 leading-relaxed wrap-break-words">
                        {{ toast.message }}
                    </p>

                    <!-- Dismiss button -->
                    <button
                        type="button"
                        class="shrink-0 text-slate-500 hover:text-slate-200 p-1 rounded-lg hover:bg-slate-800/80 transition-colors"
                        aria-label="Dismiss"
                        @click="dismiss(toast.id)"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
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
    success: {
        container: "border-emerald-500/30 text-emerald-400 shadow-emerald-950/20",
        badge: "bg-emerald-500/15 text-emerald-400 ring-emerald-500/30",
    },
    error: {
        container: "border-rose-500/30 text-rose-400 shadow-rose-950/20",
        badge: "bg-rose-500/15 text-rose-400 ring-rose-500/30",
    },
    warning: {
        container: "border-amber-500/30 text-amber-400 shadow-amber-950/20",
        badge: "bg-amber-500/15 text-amber-400 ring-amber-500/30",
    },
    info: {
        container: "border-indigo-500/30 text-indigo-400 shadow-indigo-950/20",
        badge: "bg-indigo-500/15 text-indigo-400 ring-indigo-500/30",
    },
};
</script>

<style scoped>
/* Slide-in from right with scale + glow fade */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(36px) scale(0.95);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(36px) scale(0.95);
}
.toast-leave-active {
    position: absolute;
    width: 100%;
}
.toast-move {
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
