<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <h1 class="logo">Admin</h1>
      <nav class="nav-links">
        <router-link to="/admin/dashboard">Dashboard</router-link>
        <router-link to="/admin/reservas">Reservas</router-link>
        <router-link to="/admin/habitaciones">Habitaciones</router-link>
      </nav>
      <button @click="logout" class="btn-logout">Logout</button>
    </aside>
    <main class="main-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

function logout() {
  localStorage.removeItem('admin-token');
  router.push('/admin/login');
}
</script>

<style scoped>
.admin-layout {
  display: flex;
  min-height: 100vh;
}
.sidebar {
  width: 250px;
  background: #2c3e50;
  color: white;
  display: flex;
  flex-direction: column;
  padding: 1.5rem;
}
.logo {
  font-size: 2rem;
  font-weight: bold;
  text-align: center;
  margin-bottom: 2rem;
}
.nav-links {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  flex-grow: 1;
}
.nav-links a {
  color: white;
  text-decoration: none;
  font-size: 1.1rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  transition: background 0.3s;
}
.nav-links a:hover,
.nav-links a.router-link-exact-active {
  background: #34495e;
}
.main-content {
  flex-grow: 1;
  padding: 2rem;
  background-color: #f0f2f5;
}
.btn-logout {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 0.75rem;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s;
}
.btn-logout:hover {
  background: #c0392b;
}
</style>
