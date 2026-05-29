<template>
  <div>
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Ajustes del sitio</h1>

    <div class="max-w-2xl space-y-8">
      <!-- General -->
      <section class="bg-white rounded-2xl border shadow-sm p-6">
        <h2 class="font-semibold text-gray-700 mb-4">General</h2>
        <div class="space-y-4">
          <div>
            <label class="label">Nombre del sitio</label>
            <input v-model="local.site_name" class="input" />
          </div>
          <div>
            <label class="label">Descripción</label>
            <textarea v-model="local.site_description" class="input" rows="2"></textarea>
          </div>
          <div>
            <label class="label">URL del logo</label>
            <input v-model="local.logo_url" class="input" placeholder="https://..." />
          </div>
        </div>
      </section>

      <!-- Apariencia -->
      <section class="bg-white rounded-2xl border shadow-sm p-6">
        <h2 class="font-semibold text-gray-700 mb-4">Apariencia</h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Color primario</label>
            <div class="flex items-center gap-2">
              <input v-model="local.primary_color" type="color" class="h-10 w-16 rounded border p-1 cursor-pointer" />
              <input v-model="local.primary_color" class="input flex-1" placeholder="#3B82F6" />
            </div>
          </div>
          <div>
            <label class="label">Color secundario</label>
            <div class="flex items-center gap-2">
              <input v-model="local.secondary_color" type="color" class="h-10 w-16 rounded border p-1 cursor-pointer" />
              <input v-model="local.secondary_color" class="input flex-1" placeholder="#10B981" />
            </div>
          </div>
        </div>
        <div class="mt-4">
          <label class="label">Tipografía (Google Font)</label>
          <select v-model="local.font_family" class="input">
            <option>Inter</option>
            <option>Roboto</option>
            <option>Poppins</option>
            <option>Lato</option>
            <option>Open Sans</option>
            <option>Montserrat</option>
          </select>
        </div>
      </section>

      <!-- Reservas -->
      <section class="bg-white rounded-2xl border shadow-sm p-6">
        <h2 class="font-semibold text-gray-700 mb-4">Reservas</h2>
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <input v-model="local.require_confirmation" type="checkbox" id="req-confirm" />
            <label for="req-confirm" class="text-sm text-gray-700">Requerir confirmación manual del gestor</label>
          </div>
          <div>
            <label class="label">Máximo de días de antelación</label>
            <input v-model.number="local.max_days_advance" type="number" min="1" class="input w-32" />
          </div>
        </div>
      </section>

      <p v-if="saved" class="text-green-600 font-medium">✓ Ajustes guardados correctamente</p>
      <p v-if="error" class="text-red-500">{{ error }}</p>

      <button @click="save" :disabled="saving" class="btn-primary px-8">
        {{ saving ? 'Guardando...' : 'Guardar cambios' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useSettingsStore } from '../../stores/settings';

const store  = useSettingsStore();
const local  = ref({});
const saving = ref(false);
const saved  = ref(false);
const error  = ref('');

onMounted(async () => {
  await store.fetch();
  local.value = { ...store.settings };
});

async function save() {
  saving.value = true;
  error.value  = '';
  saved.value  = false;
  try {
    const settingsArray = Object.entries(local.value).map(([key, value]) => ({ key, value: String(value) }));
    await store.save(settingsArray);
    saved.value = true;
    setTimeout(() => (saved.value = false), 3000);
  } catch (e) {
    error.value = 'Error al guardar los ajustes';
  } finally {
    saving.value = false;
  }
}
</script>

<style scoped>
@reference "../../../css/app.css";
.label       { @apply block text-sm font-medium text-gray-700 mb-1; }
.input       { @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500; }
.btn-primary { @apply bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition disabled:opacity-50; }
</style>
