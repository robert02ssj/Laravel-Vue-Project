<template>
  <div class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Recursos disponibles</h1>

    <!-- Filtros -->
    <div class="flex flex-wrap gap-3 mb-8">
      <button
        v-for="cat in categories" :key="cat.id"
        @click="filterCategory = filterCategory === cat.slug ? '' : cat.slug"
        class="px-4 py-2 rounded-full text-sm font-medium border transition"
        :class="filterCategory === cat.slug ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
      >
        {{ cat.name }}
      </button>
    </div>

    <div v-if="loading" class="text-center py-16 text-gray-400">Cargando...</div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="res in filtered" :key="res.id"
        class="bg-white rounded-2xl shadow-sm border overflow-hidden hover:shadow-md transition"
      >
        <div class="h-2" :style="{ background: res.category?.color ?? '#3B82F6' }"></div>
        <div class="p-6">
          <div class="flex items-start justify-between mb-3">
            <h3 class="font-semibold text-gray-800 text-lg">{{ res.name }}</h3>
            <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded-full">{{ res.category?.name }}</span>
          </div>
          <p class="text-sm text-gray-500 mb-4">{{ res.description }}</p>
          <div class="text-xs text-gray-400 space-y-1 mb-4">
            <p v-if="res.location">📍 {{ res.location }}</p>
            <p>👥 Capacidad: {{ res.capacity }}</p>
            <p>⏰ {{ res.open_time }} – {{ res.close_time }}</p>
            <p v-if="res.price_per_slot > 0">💶 {{ res.price_per_slot }} €/slot</p>
            <p v-else>🆓 Gratuito</p>
          </div>
          <button @click="selectResource(res)" class="btn-primary w-full text-sm">
            Reservar
          </button>
        </div>
      </div>
    </div>

    <!-- Modal reserva -->
    <ReservationModal v-if="selected" :resource="selected" @close="selected = null" @reserved="onReserved" />
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import ReservationModal from '../components/ReservationModal.vue';
import { useAuthStore } from '../stores/auth';

const route    = useRoute();
const router   = useRouter();
const auth     = useAuthStore();
const resources  = ref([]);
const categories = ref([]);
const filterCategory = ref(route.query.categoria || '');
const loading  = ref(true);
const selected = ref(null);

const filtered = computed(() =>
  filterCategory.value
    ? resources.value.filter((r) => r.category?.slug === filterCategory.value)
    : resources.value
);

onMounted(async () => {
  const [resData, catData] = await Promise.all([
    axios.get('/api/resources'),
    axios.get('/api/categories'),
  ]);
  resources.value  = resData.data;
  categories.value = catData.data;
  loading.value    = false;
});

function selectResource(res) {
  if (!auth.isLoggedIn) return router.push('/login');
  selected.value = res;
}

function onReserved() {
  selected.value = null;
}
</script>

<style scoped>
@reference "../../css/app.css";
.btn-primary { @apply bg-blue-600 text-white py-2 rounded-xl font-medium hover:bg-blue-700 transition; }
</style>
