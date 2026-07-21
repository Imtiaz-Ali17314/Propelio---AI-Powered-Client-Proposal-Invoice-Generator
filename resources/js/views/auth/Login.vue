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
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Login to Propelio</h1>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                </div>

                <button
                    type="submit"
                    :disabled="authStore.isLoading"
                    class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition disabled:opacity-50"
                >
                    {{ authStore.isLoading ? 'Logging in...' : 'Login' }}
                </button>
            </form>

            <p class="text-sm text-gray-600 mt-4">
                Don't have an account?
                <router-link :to="{ name: 'register' }" class="text-indigo-600 font-medium">Register</router-link>
            </p>
        </div>
    </div>
</template>