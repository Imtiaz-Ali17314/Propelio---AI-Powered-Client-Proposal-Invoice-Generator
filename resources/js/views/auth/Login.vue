<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';

const authStore = useAuthStore();
const router = useRouter();
const toast = useToast();

const form = ref({
    email: '',
    password: '',
});

async function handleSubmit() {
    try {
        await authStore.login(form.value);
        toast.success('Welcome back!');
        router.push({ name: 'dashboard' });
    } catch (err) {
        toast.error('Invalid email or password.');
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-950 text-slate-100 p-4 font-sans relative overflow-hidden selection:bg-indigo-500 selection:text-white">
        <!-- Ambient Background Glows -->
        <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-96 h-96 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-violet-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md bg-slate-900/80 border border-slate-800/80 p-8 rounded-3xl shadow-2xl backdrop-blur-xl relative z-10">
            <!-- Brand Logo Header -->
            <div class="text-center mb-8">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-purple-500 flex items-center justify-center text-white font-extrabold text-2xl mx-auto shadow-lg shadow-indigo-500/30 ring-1 ring-white/20 mb-3">
                    P
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-white">Welcome back to Propelio</h1>
                <p class="text-slate-400 text-xs mt-1">Sign in to your AI Proposal & Invoice engine</p>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-5">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Email Address</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        placeholder="you@agency.com"
                        class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        placeholder="••••••••"
                        class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="authStore.isLoading"
                    class="w-full bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-200 active:scale-[0.99] ring-1 ring-white/20"
                >
                    {{ authStore.isLoading ? 'Authenticating...' : 'Sign In →' }}
                </button>
            </form>

            <div class="mt-6 text-center pt-6 border-t border-slate-800/80">
                <p class="text-xs text-slate-400">
                    Don't have an account?
                    <router-link :to="{ name: 'register' }" class="text-indigo-400 font-bold hover:text-indigo-300 ml-1">Create Account</router-link>
                </p>
            </div>
        </div>
    </div>
</template>