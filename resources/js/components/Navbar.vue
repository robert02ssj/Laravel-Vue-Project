<template>
  <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
      <router-link to="/" class="flex items-center gap-2 font-bold text-xl" :style="{ color: settings.primaryColor }">
        <img v-if="settings.logoUrl" :src="settings.logoUrl" class="h-8 w-8 object-contain" alt="logo" />
        <span>{{ settings.siteName }}</span>
      </router-link>

      <div class="hidden md:flex items-center gap-6 text-sm font-medium">
        <router-link to="/recursos" class="text-gray-600 hover:text-blue-600 transition">Recursos</router-link>
        <router-link v-if="auth.isLoggedIn" to="/calendario" class="text-gray-600 hover:text-blue-600 transition">Calendario</router-link>
        <router-link v-if="auth.isLoggedIn" to="/mis-reservas" class="text-gray-600 hover:text-blue-600 transition">Mis Reservas</router-link>
        <router-link v-if="auth.isManager" to="/admin" class="text-gray-600 hover:text-blue-600 transition">Administración</router-link>
      </div>

      <div class="flex items-center gap-3">
        <template v-if="auth.isLoggedIn">
          <span class="text-sm text-gray-600 hidden md:inline">{{ auth.user?.name }}</span>
          <button @click="logout" class="btn-outline text-sm">Salir</button>
        </template>
        <template v-else>
          <router-link to="/login"    class="btn-outline text-sm">Acceder</router-link>
          <router-link to="/register" class="btn-primary text-sm">Registrarse</router-link>
        </template>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '../stores/auth';
import { useSettingsStore } from '../stores/settings';
import { useRouter } from 'vue-router';

const auth     = useAuthStore();
const settings = useSettingsStore();
const router   = useRouter();

async function logout() {
  await auth.logout();
  router.push('/login');
}
</script>

<style scoped>
@reference "../../css/app.css";
.btn-primary {
  @apply bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition;
}
.btn-outline {
  @apply border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition;
}
</style>
