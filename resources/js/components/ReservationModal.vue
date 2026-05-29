<template>
  <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-800">Reservar: {{ resource.name }}</h2>
        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
      </div>

      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="label">Fecha</label>
          <input v-model="form.date" type="date" required :min="today" class="input" @change="loadSlots" />
        </div>

        <div v-if="slots.length">
          <label class="label">Franja horaria</label>
          <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto">
            <button
              v-for="slot in slots" :key="slot.start" type="button"
              @click="selectSlot(slot)"
              :disabled="!slot.available"
              class="py-2 px-3 text-xs rounded-lg border transition"
              :class="selectedSlot?.start === slot.start
                ? 'bg-blue-600 text-white border-blue-600'
                : slot.available
                  ? 'border-gray-200 hover:border-blue-400 text-gray-700'
                  : 'bg-gray-100 text-gray-300 cursor-not-allowed border-gray-100'"
            >
              {{ fmtTime(slot.start) }} – {{ fmtTime(slot.end) }}
            </button>
          </div>
        </div>

        <div>
          <label class="label">Notas (opcional)</label>
          <textarea v-model="form.notes" class="input" rows="2" placeholder="Motivo o comentarios..."></textarea>
        </div>

        <div v-if="selectedSlot && resource.price_per_slot > 0" class="text-sm text-gray-600 bg-gray-50 rounded-lg p-3">
          Precio estimado: <strong>{{ resource.price_per_slot }} €</strong>
        </div>

        <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>

        <button type="submit" :disabled="!selectedSlot || loading" class="btn-primary w-full">
          {{ loading ? 'Reservando...' : 'Confirmar reserva' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props  = defineProps({ resource: Object });
const emits  = defineEmits(['close', 'reserved']);

const today       = new Date().toISOString().split('T')[0];
const form        = ref({ date: '', notes: '' });
const slots       = ref([]);
const selectedSlot = ref(null);
const loading     = ref(false);
const error       = ref('');

async function loadSlots() {
  slots.value       = [];
  selectedSlot.value = null;
  if (!form.value.date) return;
  const { data } = await axios.get(`/api/resources/${props.resource.id}/availability`, {
    params: { date: form.value.date },
  });
  slots.value = data;
}

function selectSlot(slot) {
  if (!slot.available) return;
  selectedSlot.value = slot;
}

function fmtTime(dt) {
  return new Date(dt).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
}

async function submit() {
  error.value   = '';
  loading.value = true;
  try {
    await axios.post('/api/reservations', {
      resource_id: props.resource.id,
      start_time:  selectedSlot.value.start,
      end_time:    selectedSlot.value.end,
      notes:       form.value.notes,
    });
    emits('reserved');
  } catch (e) {
    error.value = e.response?.data?.message || 'Error al crear la reserva';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
@reference "../../css/app.css";
.label { @apply block text-sm font-medium text-gray-700 mb-1; }
.input { @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500; }
.btn-primary { @apply bg-blue-600 text-white py-2 rounded-xl font-medium hover:bg-blue-700 transition disabled:opacity-50; }
</style>
