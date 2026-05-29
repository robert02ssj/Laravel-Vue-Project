<template>
  <div>
    <!-- Hero -->
    <section class="bg-gradient-to-br from-blue-600 to-blue-800 text-white py-20 px-4">
      <div class="max-w-4xl mx-auto text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">{{ settings.siteName }}</h1>
        <p class="text-xl text-blue-100 mb-8">{{ settings.settings.site_description }}</p>
        <div class="flex justify-center gap-4 flex-wrap">
          <router-link to="/recursos" class="bg-white text-blue-700 font-semibold px-6 py-3 rounded-xl hover:bg-blue-50 transition">
            Ver recursos
          </router-link>
          <router-link v-if="!auth.isLoggedIn" to="/register" class="border-2 border-white text-white font-semibold px-6 py-3 rounded-xl hover:bg-white/10 transition">
            Crear cuenta
          </router-link>
          <router-link v-else to="/calendario" class="border-2 border-white text-white font-semibold px-6 py-3 rounded-xl hover:bg-white/10 transition">
            Ver calendario
          </router-link>
        </div>
      </div>
    </section>

    <!-- Categorías -->
    <section class="max-w-7xl mx-auto px-4 py-16">
      <h2 class="text-2xl font-bold text-gray-800 mb-8">Categorías disponibles</h2>
      <div v-if="loading" class="text-center py-12 text-gray-400">Cargando...</div>
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <router-link
          v-for="cat in categories"
          :key="cat.id"
          :to="`/recursos?categoria=${cat.slug}`"
          class="bg-white rounded-2xl shadow-sm border p-6 hover:shadow-md transition flex items-start gap-4"
        >
          <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-xl font-bold" :style="{ background: cat.color }">
            {{ cat.name.charAt(0) }}
          </div>
          <div>
            <h3 class="font-semibold text-gray-800">{{ cat.name }}</h3>
            <p class="text-sm text-gray-500 mt-1">{{ cat.resources_count }} recursos</p>
          </div>
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useSettingsStore } from '../stores/settings';

const auth       = useAuthStore();
const settings   = useSettingsStore();
const categories = ref([]);
const loading    = ref(true);

onMounted(async () => {
  const { data } = await axios.get('/api/categories');
  categories.value = data;
  loading.value = false;
});
</script>
