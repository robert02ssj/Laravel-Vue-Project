<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Categorías</h1>
      <button @click="openForm(null)" class="btn-primary">+ Nueva categoría</button>
    </div>

    <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
          <tr>
            <th class="px-5 py-3 text-left">Nombre</th>
            <th class="px-5 py-3 text-left">Recursos</th>
            <th class="px-5 py-3 text-left">Color</th>
            <th class="px-5 py-3 text-left">Activa</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="cat in categories" :key="cat.id" class="hover:bg-gray-50">
            <td class="px-5 py-3 font-medium">{{ cat.name }}</td>
            <td class="px-5 py-3 text-gray-500">{{ cat.resources_count }}</td>
            <td class="px-5 py-3">
              <span class="inline-block w-5 h-5 rounded" :style="{ background: cat.color }"></span>
            </td>
            <td class="px-5 py-3">
              <span :class="cat.active ? 'text-green-600' : 'text-gray-400'">{{ cat.active ? 'Sí' : 'No' }}</span>
            </td>
            <td class="px-5 py-3 flex gap-2 justify-end">
              <button @click="openForm(cat)" class="text-blue-600 hover:underline">Editar</button>
              <button @click="remove(cat)" class="text-red-500 hover:underline">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Formulario modal -->
    <div v-if="showForm" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl">
        <h2 class="font-bold text-lg mb-4">{{ editing ? 'Editar' : 'Nueva' }} categoría</h2>
        <form @submit.prevent="save" class="space-y-4">
          <div>
            <label class="label">Nombre</label>
            <input v-model="form.name" required class="input" />
          </div>
          <div>
            <label class="label">Descripción</label>
            <textarea v-model="form.description" class="input" rows="2"></textarea>
          </div>
          <div class="flex gap-4">
            <div class="flex-1">
              <label class="label">Icono (clase CSS)</label>
              <input v-model="form.icon" class="input" placeholder="mdi-office-building" />
            </div>
            <div>
              <label class="label">Color</label>
              <input v-model="form.color" type="color" class="input h-10 p-1 w-16" />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="form.active" type="checkbox" id="active" />
            <label for="active" class="text-sm text-gray-700">Activa</label>
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

const categories = ref([]);
const showForm   = ref(false);
const editing    = ref(null);
const error      = ref('');
const form       = ref({ name: '', description: '', icon: '', color: '#3B82F6', active: true });

async function load() {
  const { data } = await axios.get('/api/categories');
  categories.value = data;
}

function openForm(cat) {
  editing.value = cat;
  form.value    = cat ? { ...cat } : { name: '', description: '', icon: '', color: '#3B82F6', active: true };
  showForm.value = true;
  error.value    = '';
}

async function save() {
  error.value = '';
  try {
    if (editing.value) {
      await axios.put(`/api/categories/${editing.value.id}`, form.value);
    } else {
      await axios.post('/api/categories', form.value);
    }
    showForm.value = false;
    await load();
  } catch (e) {
    error.value = e.response?.data?.message || 'Error al guardar';
  }
}

async function remove(cat) {
  if (!confirm(`¿Eliminar "${cat.name}"?`)) return;
  await axios.delete(`/api/categories/${cat.id}`);
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
