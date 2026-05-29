<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Gestión de usuarios</h1>

    <div class="bg-white rounded-2xl border shadow-sm overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="px-5 py-3 text-left">Nombre</th>
            <th class="px-5 py-3 text-left">Email</th>
            <th class="px-5 py-3 text-left">Rol</th>
            <th class="px-5 py-3 text-left">Registro</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-medium">{{ u.name }}</td>
            <td class="px-5 py-3 text-gray-500">{{ u.email }}</td>
            <td class="px-5 py-3">
              <select :value="u.roles?.[0]?.name" @change="updateRole(u, $event.target.value)" class="input-sm">
                <option value="user">Usuario</option>
                <option value="manager">Gestor</option>
                <option value="admin">Admin</option>
              </select>
            </td>
            <td class="px-5 py-3 text-gray-400">{{ fmtDate(u.created_at) }}</td>
            <td class="px-5 py-3">
              <button v-if="u.id !== currentUser.id" @click="remove(u)" class="text-red-500 hover:underline text-xs">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Paginación -->
    <div v-if="meta.last_page > 1" class="flex justify-center gap-2 mt-6">
      <button v-for="p in meta.last_page" :key="p" @click="page = p; load()" class="w-8 h-8 rounded-full text-sm border transition"
        :class="page === p ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-300 text-gray-600 hover:bg-gray-50'"
      >{{ p }}</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const currentUser = auth.user;
const users = ref([]);
const page  = ref(1);
const meta  = ref({ last_page: 1 });

async function load() {
  const { data } = await axios.get('/api/admin/users', { params: { page: page.value } });
  users.value = data.data;
  meta.value  = data.meta ?? { last_page: 1 };
}

async function updateRole(u, role) {
  await axios.put(`/api/admin/users/${u.id}`, { role });
  await load();
}

async function remove(u) {
  if (!confirm(`¿Eliminar a "${u.name}"?`)) return;
  await axios.delete(`/api/admin/users/${u.id}`);
  await load();
}

function fmtDate(d) {
  return new Date(d).toLocaleDateString('es-ES');
}

onMounted(load);
</script>

<style scoped>
@reference "../../../css/app.css";
.input-sm { @apply border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500; }
</style>
