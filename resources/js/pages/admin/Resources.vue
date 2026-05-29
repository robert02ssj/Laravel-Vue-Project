<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Recursos</h1>
      <button @click="openForm(null)" class="btn-primary">+ Nuevo recurso</button>
    </div>

    <div class="bg-white rounded-2xl border shadow-sm overflow-x-auto">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="px-5 py-3 text-left">Nombre</th>
            <th class="px-5 py-3 text-left">Categoría</th>
            <th class="px-5 py-3 text-left">Horario</th>
            <th class="px-5 py-3 text-left">Precio</th>
            <th class="px-5 py-3 text-left">Activo</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="r in resources" :key="r.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-medium">{{ r.name }}</td>
            <td class="px-5 py-3 text-gray-500">{{ r.category?.name }}</td>
            <td class="px-5 py-3 text-gray-500">{{ r.open_time }} – {{ r.close_time }}</td>
            <td class="px-5 py-3">{{ r.price_per_slot > 0 ? r.price_per_slot + '€' : 'Gratis' }}</td>
            <td class="px-5 py-3">
              <span :class="r.active ? 'text-green-600' : 'text-gray-400'">{{ r.active ? 'Sí' : 'No' }}</span>
            </td>
            <td class="px-5 py-3 flex gap-2 justify-end">
              <button @click="openForm(r)" class="text-blue-600 hover:underline">Editar</button>
              <button @click="remove(r)" class="text-red-500 hover:underline">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4 overflow-y-auto">
      <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-xl my-8">
        <h2 class="font-bold text-lg mb-4">{{ editing ? 'Editar' : 'Nuevo' }} recurso</h2>
        <form @submit.prevent="save" class="space-y-3">
          <div>
            <label class="label">Categoría</label>
            <select v-model="form.category_id" required class="input">
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div>
            <label class="label">Nombre</label>
            <input v-model="form.name" required class="input" />
          </div>
          <div>
            <label class="label">Descripción</label>
            <textarea v-model="form.description" class="input" rows="2"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="label">Ubicación</label>
              <input v-model="form.location" class="input" />
            </div>
            <div>
              <label class="label">Capacidad</label>
              <input v-model.number="form.capacity" type="number" min="1" class="input" />
            </div>
          </div>
          <div class="grid grid-cols-3 gap-3">
            <div>
              <label class="label">Apertura</label>
              <input v-model="form.open_time" type="time" required class="input" />
            </div>
            <div>
              <label class="label">Cierre</label>
              <input v-model="form.close_time" type="time" required class="input" />
            </div>
            <div>
              <label class="label">Slot (min)</label>
              <input v-model.number="form.slot_duration" type="number" min="15" class="input" />
            </div>
          </div>
          <div>
            <label class="label">Precio por slot (€)</label>
            <input v-model.number="form.price_per_slot" type="number" min="0" step="0.01" class="input" />
          </div>
          <div class="flex items-center gap-2">
            <input v-model="form.active" type="checkbox" id="r-active" />
            <label for="r-active" class="text-sm text-gray-700">Activo</label>
          </div>
          <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
          <div class="flex gap-3 pt-2">
            <button type="button" @click="showForm = false" class="btn-outline flex-1">Cancelar</button>
            <button type="submit" class="btn-primary flex-1">Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const resources  = ref([]);
const categories = ref([]);
const showForm   = ref(false);
const editing    = ref(null);
const error      = ref('');
const form       = ref({});

async function load() {
  const [res, cats] = await Promise.all([
    axios.get('/api/resources'),
    axios.get('/api/categories'),
  ]);
  resources.value  = res.data;
  categories.value = cats.data;
}

function openForm(r) {
  editing.value  = r;
  form.value     = r ? { ...r } : { category_id: '', name: '', description: '', location: '', capacity: 1, active: true, open_time: '08:00', close_time: '20:00', slot_duration: 60, price_per_slot: 0 };
  showForm.value = true;
  error.value    = '';
}

async function save() {
  error.value = '';
  try {
    if (editing.value) {
      await axios.put(`/api/resources/${editing.value.id}`, form.value);
    } else {
      await axios.post('/api/resources', form.value);
    }
    showForm.value = false;
    await load();
  } catch (e) {
    const errs = e.response?.data?.errors;
    error.value = errs ? Object.values(errs).flat().join(' ') : 'Error al guardar';
  }
}

async function remove(r) {
  if (!confirm(`¿Eliminar "${r.name}"?`)) return;
  await axios.delete(`/api/resources/${r.id}`);
  await load();
}

onMounted(load);
</script>

<style scoped>
@reference "../../../css/app.css";
.label       { @apply block text-sm font-medium text-gray-700 mb-1; }
.input       { @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500; }
.btn-primary { @apply bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition; }
.btn-outline  { @apply border border-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-50 transition; }
</style>
