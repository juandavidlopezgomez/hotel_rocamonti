<template>
  <nav class="navbar">
    <div class="navbar-container">
      <router-link to="/" class="navbar-brand">
        🏨 Hotel Rocamonti
      </router-link>

      <div class="navbar-menu">
        <router-link to="/" class="nav-link">Inicio</router-link>
        <router-link to="/reservar" class="nav-link">Reservar</router-link>
        <a href="#contacto" class="nav-link">Contacto</a>
        <router-link to="/admin/login" class="nav-link nav-admin">
          🔐 Administración
        </router-link>
      </div>
    </div>
  </nav>
</template>

<style scoped>
.navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  background: rgba(255, 255, 255, 0.98);
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  backdrop-filter: blur(10px);
}

.navbar-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.navbar-brand {
  font-size: 1.5rem;
  font-weight: bold;
  color: #0071c2;
  text-decoration: none;
  transition: color 0.3s;
}

.navbar-brand:hover {
  color: #005a9c;
}

.navbar-menu {
  display: flex;
  gap: 2rem;
  align-items: center;
}

.nav-link {
  text-decoration: none;
  color: #333;
  font-weight: 500;
  font-size: 1rem;
  transition: color 0.3s;
  padding: 0.5rem 1rem;
  border-radius: 4px;
}

.nav-link:hover {
  color: #0071c2;
  background: #f0f8ff;
}

.nav-link.router-link-active {
  color: #0071c2;
  background: #e6f3ff;
}

.nav-admin {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white !important;
  padding: 0.6rem 1.2rem;
  border-radius: 6px;
  font-weight: 600;
}

.nav-admin:hover {
  background: linear-gradient(135deg, #5568d3 0%, #653a8b 100%);
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(102, 126, 234, 0.4);
}

/* Responsive */
@media (max-width: 768px) {
  .navbar-menu {
    gap: 1rem;
  }

  .nav-link {
    font-size: 0.9rem;
    padding: 0.4rem 0.8rem;
  }

  .navbar-brand {
    font-size: 1.2rem;
  }
}
</style>
