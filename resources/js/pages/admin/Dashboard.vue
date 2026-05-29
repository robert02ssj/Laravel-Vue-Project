<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

    <div v-if="loading" class="text-gray-400">Cargando estadísticas...</div>

    <div v-else>
      <!-- KPIs -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
        <KpiCard label="Usuarios"    :value="stats.total_users"        color="blue" />
        <KpiCard label="Recursos"    :value="stats.total_resources"    color="green" />
        <KpiCard label="Total res."  :value="stats.total_reservations" color="gray" />
        <KpiCard label="Pendientes"  :value="stats.pending"            color="yellow" />
        <KpiCard label="Confirmadas" :value="stats.confirmed"          color="green" />
        <KpiCard label="Canceladas"  :value="stats.cancelled"          color="red" />
      </div>

      <!-- Últimas reservas -->
      <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b font-semibold text-gray-700">Últimas reservas</div>
        <table class="w-full text-sm">
          <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
            <tr>
              <th class="px-5 py-3 text-left">Usuario</th>
              <th class="px-5 py-3 text-left">Recurso</th>
              <th class="px-5 py-3 text-left">Inicio</th>
              <th class="px-5 py-3 text-left">Estado</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-for="r in stats.recent" :key="r.id" class="hover:bg-gray-50">
              <td class="px-5 py-3">{{ r.user?.name }}</td>
              <td class="px-5 py-3">{{ r.resource?.name }}</td>
              <td class="px-5 py-3">{{ fmtDt(r.start_time) }}</td>
              <td class="px-5 py-3"><StatusBadge :status="r.status" /></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import KpiCard from '../../components/KpiCard.vue';
import StatusBadge from '../../components/StatusBadge.vue';

const stats   = ref({});
const loading = ref(true);

onMounted(async () => {
  const { data } = await axios.get('/api/admin/dashboard');
  stats.value   = data;
  loading.value = false;
});

function fmtDt(dt) {
  return new Date(dt).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' });
}
</script>
