<script setup>
import AppLayout from '../components/layout/AppLayout.vue';
import { useAuthStore } from '../stores/auth';

const authStore = useAuthStore();
</script>

<template>
    <AppLayout>
        <h2 class="text-2xl font-bold text-gray-800 mb-2">
            Welcome back, {{ authStore.user?.name }} 👋
        </h2>
        <p class="text-gray-600">
            Phase 1 complete — auth is working. Dashboard widgets (proposals, invoices, revenue charts) will be built in Phase 5.
        </p>
    </AppLayout>
</template>