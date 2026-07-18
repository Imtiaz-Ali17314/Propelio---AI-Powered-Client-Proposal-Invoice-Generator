<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/auth';

const authStore = useAuthStore();
const router = useRouter();

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
        router.push({ name: 'dashboard' });
    } catch (err) {
        errors.value = authStore.error ?? {};
    }
}
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="w-full max-w-md bg-white p-8 rounded-xl shadow-md">
            <h1 class="text-2xl font-bold mb-6 text-gray-800">Create your Propelio account</h1>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Agency / Your Name</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="errors.name" class="text-red-600 text-xs mt-1">{{ errors.name[0] }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="errors.email" class="text-red-600 text-xs mt-1">{{ errors.email[0] }}</p>
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input
                        v-model="form.password_confirmation"
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
                    {{ authStore.isLoading ? 'Creating account...' : 'Register' }}
                </button>
            </form>

            <p class="text-sm text-gray-600 mt-4">
                Already have an account?
                <router-link :to="{ name: 'login' }" class="text-indigo-600 font-medium">Login</router-link>
            </p>
        </div>
    </div>
</template>