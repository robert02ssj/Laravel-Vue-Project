<template>
  <div id="app-root">
    <Navbar />
    <main class="min-h-screen bg-gray-50">
      <router-view />
    </main>
    <footer class="bg-white border-t py-4 text-center text-sm text-gray-400">
      {{ settingsStore.siteName }} &copy; {{ new Date().getFullYear() }}
    </footer>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import Navbar from './components/Navbar.vue';
import { useSettingsStore } from './stores/settings';
import { useAuthStore } from './stores/auth';

const settingsStore = useSettingsStore();
const authStore = useAuthStore();

onMounted(async () => {
  await settingsStore.fetch();
  if (authStore.isLoggedIn) {
    await authStore.fetchMe().catch(() => authStore.clearSession());
  }
});
</script>
