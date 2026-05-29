<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm border p-8">
      <h1 class="text-2xl font-bold text-gray-800 mb-6">Acceder</h1>
      <form @submit.prevent="submit" class="space-y-4">
        <div>
          <label class="label">Correo</label>
          <input v-model="form.email" type="email" required class="input" placeholder="tu@email.com" />
        </div>
        <div>
          <label class="label">Contraseña</label>
          <input v-model="form.password" type="password" required class="input" placeholder="••••••••" />
        </div>
        <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
        <button type="submit" :disabled="loading" class="btn-primary w-full">
          {{ loading ? 'Cargando...' : 'Entrar' }}
        </button>
      </form>
      <p class="text-center text-sm text-gray-500 mt-6">
        ¿Sin cuenta? <router-link to="/register" class="text-blue-600">Regístrate</router-link>
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
const form   = ref({ email: '', password: '' });
const error  = ref('');
const loading = ref(false);

async function submit() {
  error.value   = '';
  loading.value = true;
  try {
    await auth.login(form.value.email, form.value.password);
    router.push('/');
  } catch (e) {
    error.value = e.response?.data?.message || 'Error al iniciar sesión';
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
