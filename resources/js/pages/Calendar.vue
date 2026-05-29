<template>
  <div class="max-w-7xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Calendario de reservas</h1>
    <div class="bg-white rounded-2xl shadow-sm border p-4">
      <FullCalendar :options="calendarOptions" />
    </div>

    <!-- Detalle reserva -->
    <div v-if="detail" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 px-4">
      <div class="bg-white rounded-2xl p-6 w-full max-w-sm shadow-xl">
        <h2 class="font-bold text-lg mb-3">{{ detail.title }}</h2>
        <p class="text-sm text-gray-600 mb-1">🕐 {{ fmtDt(detail.start) }} – {{ fmtDt(detail.end) }}</p>
        <p class="text-sm text-gray-600 mb-1">Estado: <span class="font-medium">{{ detail.extendedProps?.status }}</span></p>
        <p class="text-sm text-gray-600">Usuario: {{ detail.extendedProps?.user }}</p>
        <button @click="detail = null" class="mt-4 btn-primary w-full">Cerrar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin   from '@fullcalendar/daygrid';
import timeGridPlugin  from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';
import axios from 'axios';

const detail = ref(null);

const calendarOptions = ref({
  plugins:     [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'timeGridWeek',
  locale:      esLocale,
  headerToolbar: {
    left:   'prev,next today',
    center: 'title',
    right:  'dayGridMonth,timeGridWeek,timeGridDay',
  },
  events: async (info, success) => {
    const { data } = await axios.get('/api/reservations/calendar', {
      params: { start: info.startStr, end: info.endStr },
    });
    success(data);
  },
  eventClick: ({ event }) => {
    detail.value = event;
  },
  height: 'auto',
});

function fmtDt(dt) {
  if (!dt) return '';
  return new Date(dt).toLocaleString('es-ES', { dateStyle: 'short', timeStyle: 'short' });
}
</script>

<style scoped>
@reference "../../css/app.css";
.btn-primary { @apply bg-blue-600 text-white py-2 rounded-xl font-medium hover:bg-blue-700 transition; }
</style>
