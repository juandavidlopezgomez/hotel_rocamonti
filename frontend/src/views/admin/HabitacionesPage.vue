<template>
  <div>
    <h1>Habitaciones</h1>
    <div v-if="loading">Loading habitaciones...</div>
    <div v-if="error">{{ error }}</div>
    <div v-if="habitaciones" class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Número</th>
            <th>Tipo</th>
            <th>Piso</th>
            <th>Estado</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="habitacion in habitaciones" :key="habitacion.id">
            <td>{{ habitacion.id }}</td>
            <td>{{ habitacion.numero_habitacion }}</td>
            <td>{{ habitacion.tipo_habitacion.nombre }}</td>
            <td>{{ habitacion.piso }}</td>
            <td><span :class="'status-' + habitacion.estado">{{ habitacion.estado }}</span></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';

const habitaciones = ref(null);
const loading = ref(false);
const error = ref(null);

async function fetchHabitaciones() {
  loading.value = true;
  try {
    const response = await api.get('/admin/habitaciones');
    habitaciones.value = response.data.data;
  } catch (err) {
    error.value = 'Failed to load habitaciones.';
    console.error(err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchHabitaciones();
});
</script>

<style scoped>
.table-container {
  background: white;
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  overflow: hidden;
  margin-top: 2rem;
}
table {
  width: 100%;
  border-collapse: collapse;
}
thead tr {
  background: #f8f9fa;
}
th, td {
  padding: 1rem;
  text-align: left;
  border-bottom: 1px solid #e9ecef;
}
th {
  font-weight: 600;
  color: #555;
}
.status-disponible {
  background: #10b981;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.9rem;
}
.status-ocupada {
  background: #ef4444;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.9rem;
}
</style>
