<template>
  <div class="max-w-4xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Mis reservas</h1>

    <div v-if="loading" class="text-center py-16 text-gray-400">Cargando...</div>

    <div v-else-if="reservations.length === 0" class="text-center py-16 text-gray-400">
      No tienes reservas aún. <router-link to="/recursos" class="text-blue-600">Reserva un recurso</router-link>
    </div>

    <div v-else class="space-y-4">
      <div
        v-for="res in reservations" :key="res.id"
        class="bg-white rounded-2xl shadow-sm border p-5 flex items-center justify-between gap-4"
      >
        <div>
          <div class="flex items-center gap-2 mb-1">
            <div class="w-3 h-3 rounded-full" :style="{ background: res.resource?.category?.color }"></div>
            <h3 class="font-semibold text-gray-800">{{ res.resource?.name }}</h3>
            <StatusBadge :status="res.status" />
          </div>
          <p class="text-sm text-gray-500">
            {{ fmtDt(res.start_time) }} → {{ fmtDt(res.end_time) }}
          </p>
          <p v-if="res.notes" class="text-xs text-gray-400 mt-1">{{ res.notes }}</p>
        </div>
        <button
          v-if="res.status !== 'cancelled'"
          @click="cancel(res)"
          class="text-sm text-red-500 hover:text-red-700 border border-red-200 hover:border-red-400 px-3 py-1 rounded-lg transition"
        >
          Cancelar
        </button>
      </div>
    </div>

    <!-- Paginación -->
    <div v-if="meta.last_page > 1" class="flex justify-center gap-2 mt-8">
      <button
        v-for="p in meta.last_page" :key="p"
        @click="page = p; fetch()"
        class="w-8 h-8 rounded-full text-sm border transition"
        :class="page === p ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
      >{{ p }}</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import StatusBadge from '../components/StatusBadge.vue';

const reservations = ref([]);
const loading = ref(true);
const page    = ref(1);
const meta    = ref({ last_page: 1 });

async function fetch() {
  loading.value = true;
  const { data } = await axios.get('/api/reservations', { params: { page: page.value } });
  reservations.value = data.data;
  meta.value         = data.meta ?? { last_page: 1 };
  loading.value      = false;
}

async function cancel(res) {
  if (!confirm('¿Cancelar esta reserva?')) return;
  await axios.delete(`/api/reservations/${res.id}`);
  await fetch();
}

function fmtDt(dt) {
  return new Date(dt).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' });
}

onMounted(fetch);
</script>
