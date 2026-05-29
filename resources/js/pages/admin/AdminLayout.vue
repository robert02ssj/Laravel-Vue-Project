<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="w-56 bg-white border-r flex-shrink-0 hidden md:flex flex-col">
      <div class="px-4 py-5 font-bold text-blue-600 border-b">Panel Admin</div>
      <nav class="p-3 space-y-1 flex-1">
        <router-link to="/admin"           exact class="nav-link">Dashboard</router-link>
        <router-link to="/admin/recursos"  class="nav-link">Recursos</router-link>
        <router-link to="/admin/categorias" class="nav-link">Categorías</router-link>
        <router-link to="/admin/reservas"  class="nav-link">Reservas</router-link>
        <router-link to="/admin/usuarios"  class="nav-link">Usuarios</router-link>
        <router-link v-if="auth.isAdmin" to="/admin/ajustes" class="nav-link">Ajustes del sitio</router-link>
      </nav>
    </aside>

    <!-- Contenido -->
    <div class="flex-1 min-w-0">
      <div class="max-w-6xl mx-auto px-4 py-8">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../../stores/auth';
const auth = useAuthStore();
</script>

<style scoped>
@reference "../../../css/app.css";
.nav-link {
  @apply flex items-center gap-2 px-3 py-2 rounded-lg text-sm text-gray-600 hover:bg-blue-50 hover:text-blue-700 transition;
}
.router-link-active.nav-link {
  @apply bg-blue-50 text-blue-700 font-medium;
}
</style>
