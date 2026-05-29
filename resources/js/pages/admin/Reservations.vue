<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Gestión de reservas</h1>

    <!-- Filtros -->
    <div class="flex flex-wrap gap-3 mb-5">
      <select v-model="filterStatus" @change="load" class="input w-auto">
        <option value="">Todos los estados</option>
        <option value="pending">Pendiente</option>
        <option value="confirmed">Confirmada</option>
        <option value="cancelled">Cancelada</option>
      </select>
    </div>

    <div class="bg-white rounded-2xl border shadow-sm overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="px-5 py-3 text-left">#</th>
            <th class="px-5 py-3 text-left">Usuario</th>
            <th class="px-5 py-3 text-left">Recurso</th>
            <th class="px-5 py-3 text-left">Inicio</th>
            <th class="px-5 py-3 text-left">Fin</th>
            <th class="px-5 py-3 text-left">Estado</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="r in reservations" :key="r.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 text-gray-400">{{ r.id }}</td>
            <td class="px-5 py-3">{{ r.user?.name }}</td>
            <td class="px-5 py-3">{{ r.resource?.name }}</td>
            <td class="px-5 py-3">{{ fmtDt(r.start_time) }}</td>
            <td class="px-5 py-3">{{ fmtDt(r.end_time) }}</td>
            <td class="px-5 py-3"><StatusBadge :status="r.status" /></td>
            <td class="px-5 py-3">
              <div class="flex gap-2">
                <button v-if="r.status === 'pending'" @click="changeStatus(r, 'confirmed')" class="text-green-600 hover:underline text-xs">Confirmar</button>
                <button v-if="r.status !== 'cancelled'" @click="changeStatus(r, 'cancelled')" class="text-red-500 hover:underline text-xs">Cancelar</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import StatusBadge from '../../components/StatusBadge.vue';

const reservations = ref([]);
const filterStatus = ref('');

async function load() {
  const { data } = await axios.get('/api/reservations', {
    params: { status: filterStatus.value || undefined },
  });
  reservations.value = data.data || data;
}

async function changeStatus(r, status) {
  await axios.put(`/api/reservations/${r.id}`, { status });
  await load();
}

function fmtDt(dt) {
  return new Date(dt).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' });
}

onMounted(load);
</script>

<style scoped>
@reference "../../../css/app.css";
.input { @apply border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500; }
</style>
