<template>
  <div>
    <h1>Reservas</h1>
    <div v-if="loading">Loading reservations...</div>
    <div v-if="error">{{ error }}</div>
    <div v-if="reservas" class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Cliente</th>
            <th>Habitación</th>
            <th>Fechas</th>
            <th>Estado</th>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="reserva in reservas.data" :key="reserva.id">
            <td>{{ reserva.id }}</td>
            <td>{{ reserva.cliente.nombre }} {{ reserva.cliente.apellido }}</td>
            <td>{{ reserva.habitacion.tipo_habitacion.nombre }}</td>
            <td>{{ reserva.fecha_entrada }} - {{ reserva.fecha_salida }}</td>
            <td><span :class="'status-' + reserva.estado">{{ reserva.estado }}</span></td>
            <td>{{ formatCurrency(reserva.precio_total) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '@/services/api';
import { formatCurrency } from '../../../utils/formatters';

const reservas = ref(null);
const loading = ref(false);
const error = ref(null);

async function fetchReservas() {
  loading.value = true;
  try {
    const response = await api.get('/admin/reservas');
    reservas.value = response.data.data;
  } catch (err) {
    error.value = 'Failed to load reservations.';
    console.error(err);
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchReservas();
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
.status-pagada {
  background: #10b981;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.9rem;
}
.status-cancelada {
  background: #ef4444;
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.9rem;
}
</style>
