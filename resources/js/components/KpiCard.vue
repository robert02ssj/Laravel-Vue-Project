<template>
  <div class="bg-white rounded-2xl border shadow-sm p-4 text-center">
    <p class="text-3xl font-bold" :class="`text-${color}-600`">{{ value }}</p>
    <p class="text-xs text-gray-500 mt-1">{{ label }}</p>
  </div>
</template>

<script setup>
defineProps({ label: String, value: [Number, String], color: { type: String, default: 'blue' } });
</script>
