<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useToast } from '@/composables/useToast';

const authStore = useAuthStore();
const router = useRouter();
const toast = useToast();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const errors = ref({});

async function handleSubmit() {
    errors.value = {};
    try {
        await authStore.register(form.value);
        toast.success('Account created — welcome to Propelio!');
        router.push({ name: 'dashboard' });
    } catch (err) {
        errors.value = authStore.error ?? {};
        toast.error('Please fix the errors below and try again.');
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-950 text-slate-100 p-4 font-sans relative overflow-hidden selection:bg-indigo-500 selection:text-white">
        <!-- Ambient Background Glows -->
        <div class="absolute top-1/4 right-1/2 translate-x-1/2 w-96 h-96 bg-purple-600/15 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute bottom-1/4 left-1/4 w-80 h-80 bg-indigo-600/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="w-full max-w-md bg-slate-900/80 border border-slate-800/80 p-8 rounded-3xl shadow-2xl backdrop-blur-xl relative z-10">
            <!-- Brand Logo Header -->
            <div class="text-center mb-6">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 via-violet-600 to-purple-500 flex items-center justify-center text-white font-extrabold text-2xl mx-auto shadow-lg shadow-indigo-500/30 ring-1 ring-white/20 mb-3">
                    P
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight text-white">Create Propelio Account</h1>
                <p class="text-slate-400 text-xs mt-1">Start generating proposals & invoices with AI</p>
            </div>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Agency / Your Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        placeholder="e.g. Nexus Design Agency"
                        class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                    <p v-if="errors.name" class="text-rose-400 text-xs mt-1">{{ errors.name[0] }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Email Address</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        placeholder="you@agency.com"
                        class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                    <p v-if="errors.email" class="text-rose-400 text-xs mt-1">{{ errors.email[0] }}</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        placeholder="••••••••"
                        class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1.5">Confirm Password</label>
                    <input
                        v-model="form.password_confirmation"
                        type="password"
                        required
                        placeholder="••••••••"
                        class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="authStore.isLoading"
                    class="w-full bg-gradient-to-r from-indigo-600 via-violet-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50 text-white font-semibold py-3 rounded-xl shadow-lg shadow-indigo-600/30 transition-all duration-200 active:scale-[0.99] ring-1 ring-white/20 mt-2"
                >
                    {{ authStore.isLoading ? 'Creating account...' : 'Create Account →' }}
                </button>
            </form>

            <div class="mt-6 text-center pt-5 border-t border-slate-800/80">
                <p class="text-xs text-slate-400">
                    Already registered?
                    <router-link :to="{ name: 'login' }" class="text-indigo-400 font-bold hover:text-indigo-300 ml-1">Sign In</router-link>
                </p>
            </div>
        </div>
    </div>
</template>