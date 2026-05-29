<template>
  <span class="text-xs font-medium px-2 py-0.5 rounded-full" :class="classes[status] || classes.default">
    {{ labels[status] || status }}
  </span>
</template>

<script setup>
defineProps({ status: String });

const labels = { pending: 'Pendiente', confirmed: 'Confirmada', cancelled: 'Cancelada' };
const classes = {
  pending:   'bg-yellow-100 text-yellow-700',
  confirmed: 'bg-green-100 text-green-700',
  cancelled: 'bg-red-100 text-red-500',
  default:   'bg-gray-100 text-gray-500',
};
</script>
