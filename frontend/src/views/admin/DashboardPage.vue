<template>
  <div>
    <h1>Dashboard</h1>
    <div v-if="loading">Loading...</div>
    <div v-if="error">{{ error }}</div>
    <div v-if="stats" class="stats-grid">
      <div class="stat-card">
        <h2>Total Reservas</h2>
        <p>{{ stats.total_reservas }}</p>
      </div>
      <div class="stat-card">
        <h2>Habitaciones Ocupadas</h2>
        <p>{{ stats.habitaciones_ocupadas }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';

const stats = ref(null);
const loading = ref(false);
const error = ref(null);

async function fetchDashboardStats() {
  loading.value = true;
  try {
    const response = await api.get('/admin/dashboard');
    stats.value = response.data.data;
  } catch (err) {
    error.value = 'Failed to load dashboard stats.';
    console.error(err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchDashboardStats();
});
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}
.stat-card {
  background: white;
  padding: 1.5rem;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
.stat-card h2 {
  font-size: 1.2rem;
  color: #555;
  margin-bottom: 1rem;
}
.stat-card p {
  font-size: 2.5rem;
  font-weight: bold;
  color: #0071c2;
}
</style>
