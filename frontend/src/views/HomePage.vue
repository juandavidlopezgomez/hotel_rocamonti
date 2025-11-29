<template>
  <div class="home-page">
    <section class="hero">
      <div class="hero-background">
        <div class="hero-overlay"></div>
      </div>

      <div class="hero-content">
        <h1 class="hero-title">Hotel Rocamonti</h1>
        <router-link to="/reservar" class="btn-reservar">
          Reservar Ahora
        </router-link>
      </div>
    </section>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'

const router = useRouter()
</script>

<style scoped>
.home-page {
  width: 100%;
  min-height: 100vh;
}

.hero {
  position: relative;
  width: 100%;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}

.hero-background {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-image: url('/images/portada.jpg');
  background-size: cover;
  background-position: center;
  z-index: 1;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 2;
}

.hero-content {
  position: relative;
  z-index: 3;
  text-align: center;
  padding: 2rem;
}

.hero-title {
  font-size: 5rem;
  font-weight: 700;
  color: white;
  margin-bottom: 3rem;
  text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
  letter-spacing: 2px;
}

.btn-reservar {
  display: inline-block;
  padding: 1rem 3rem;
  background-color: #f59e0b;
  color: white;
  font-size: 1.25rem;
  font-weight: 600;
  border-radius: 8px;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
}

.btn-reservar:hover {
  background-color: #d97706;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6);
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 3rem;
  }

  .btn-reservar {
    font-size: 1rem;
    padding: 0.875rem 2rem;
  }
}
</style>
