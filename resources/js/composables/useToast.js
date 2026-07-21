import { reactive } from "vue";

// Module-level (singleton) state — sab components isi ek queue ko share
// karte hain, isliye ye Pinia store ki bajaye ek simple composable hai.
// Jaisa hi ye module pehli baar import hota hai, `toasts` array bante hai
// aur har jagah se useToast() call karne par wohi ek instance milti hai.
const toasts = reactive([]);

let nextId = 1;

const DEFAULT_DURATION = 4000; // ms

function dismiss(id) {
    const index = toasts.findIndex((t) => t.id === id);
    if (index !== -1) toasts.splice(index, 1);
}

function push(type, message, duration = DEFAULT_DURATION) {
    const id = nextId++;
    toasts.push({ id, type, message });

    if (duration > 0) {
        setTimeout(() => dismiss(id), duration);
    }

    return id;
}

export function useToast() {
    return {
        toasts,
        dismiss,
        success: (message, duration) => push("success", message, duration),
        error: (message, duration) => push("error", message, duration),
        warning: (message, duration) => push("warning", message, duration),
        info: (message, duration) => push("info", message, duration),
    };
}
