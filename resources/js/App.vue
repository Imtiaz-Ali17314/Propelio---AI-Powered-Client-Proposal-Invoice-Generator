<script setup>
import { onMounted } from 'vue';
import { useAuthStore } from './stores/auth';
import ToastContainer from './components/ui/ToastContainer.vue';

const authStore = useAuthStore();

onMounted(() => {
    authStore.fetchUser();
});
</script>

<template>
    <router-view />
    <ToastContainer />
</template>