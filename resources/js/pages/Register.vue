<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm border p-8">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Crear cuenta</h1>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="label">Nombre</label>
          <input v-model="form.name" type="text" required class="input" placeholder="Tu nombre" />
        </div>
        <div>
          <label class="label">Correo</label>
          <input v-model="form.email" type="email" required class="input" placeholder="tu@email.com" />
        </div>
        <div>
          <label class="label">Contraseña</label>
          <input v-model="form.password" type="password" required class="input" placeholder="Mínimo 8 caracteres" />
        </div>
        <div>
          <label class="label">Confirmar contraseña</label>
          <input v-model="form.password_confirmation" type="password" required class="input" />
        </div>
        <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
        <button type="submit" :disabled="loading" class="btn-primary w-full">
          {{ loading ? 'Registrando...' : 'Registrarse' }}
        </button>
      </form>
      <p class="text-center text-sm text-gray-500 mt-6">
        ¿Ya tienes cuenta? <router-link to="/login" class="text-blue-600">Accede aquí</router-link>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth   = useAuthStore();
const router = useRouter();
const form   = ref({ name: '', email: '', password: '', password_confirmation: '' });
const error  = ref('');
const loading = ref(false);

async function submit() {
  error.value   = '';
  loading.value = true;
  try {
    await auth.register(form.value.name, form.value.email, form.value.password, form.value.password_confirmation);
    router.push('/');
  } catch (e) {
    const errs = e.response?.data?.errors;
    error.value = errs ? Object.values(errs).flat().join(' ') : 'Error al registrarse';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
@reference "../../css/app.css";
.label  { @apply block text-sm font-medium text-gray-700 mb-1; }
.input  { @apply w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500; }
.btn-primary { @apply bg-blue-600 text-white py-2 rounded-lg font-medium hover:bg-blue-700 transition disabled:opacity-50; }
</style>
