<template>
  <div class="home-page">
    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-background">
        <div class="hero-overlay"></div>
      </div>

      <!-- Navbar Integrado -->
      <nav class="hero-nav">
        <div class="hero-nav-container">
          <LogoRocamonti variant="light" width="180px" height="54px" :show-stars="false" />

          <div class="hero-nav-links desktop-only">
            <router-link to="/" class="hero-link active">Inicio</router-link>
            <router-link to="/reservar" class="hero-link">Reservar</router-link>
            <router-link to="/habitaciones" class="hero-link">Habitaciones</router-link>
            <router-link to="/contacto" class="hero-link">Contacto</router-link>
          </div>

          <router-link to="/reservar" class="btn btn-accent btn-sm">
            Reservar Ahora
          </router-link>
        </div>
      </nav>

      <!-- Hero Content -->
      <div class="hero-content">
        <div class="hero-badge animate-fadeIn">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
          </svg>
          Hotel Boutique de 5 Estrellas
        </div>

        <h1 class="hero-title animate-fadeInUp">
          Hotel Rocamonti
        </h1>

        <p class="hero-subtitle animate-fadeInUp" style="animation-delay: 0.2s">
          Tu refugio de lujo junto a la Laguna de Tota
        </p>

        <p class="hero-description animate-fadeInUp" style="animation-delay: 0.3s">
          Experimenta la perfecta combinación de elegancia y naturaleza en un entorno incomparable.
          Desconecta del mundo y reconecta contigo mismo.
        </p>

        <div class="hero-actions animate-fadeInUp" style="animation-delay: 0.4s">
          <router-link to="/reservar" class="btn btn-primary btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            Reservar Ahora
          </router-link>

          <router-link to="/habitaciones" class="btn btn-secondary btn-lg">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <polygon points="10 8 16 12 10 16 10 8"></polygon>
            </svg>
            Ver Habitaciones
          </router-link>
        </div>

        <!-- Stats -->
        <div class="hero-stats animate-fadeInUp" style="animation-delay: 0.5s">
          <div class="stat">
            <div class="stat-value">50+</div>
            <div class="stat-label">Habitaciones</div>
          </div>
          <div class="stat-divider"></div>
          <div class="stat">
            <div class="stat-value">98%</div>
            <div class="stat-label">Satisfacción</div>
          </div>
          <div class="stat-divider"></div>
          <div class="stat">
            <div class="stat-value">5★</div>
            <div class="stat-label">Calificación</div>
          </div>
        </div>
      </div>

      <!-- Scroll Indicator -->
      <div class="scroll-indicator">
        <div class="scroll-mouse">
          <div class="scroll-wheel"></div>
        </div>
        <span>Desliza para explorar</span>
      </div>
    </section>

    <!-- Features Section -->
    <section class="features-section">
      <div class="container">
        <div class="section-header">
          <span class="section-badge">Experiencia Premium</span>
          <h2>¿Por qué elegir Rocamonti?</h2>
          <p>Descubre los servicios que hacen de tu estadía una experiencia inolvidable</p>
        </div>

        <div class="features-grid">
          <div class="feature-card animate-fadeInUp">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                <circle cx="12" cy="10" r="3"></circle>
              </svg>
            </div>
            <h3>Ubicación Privilegiada</h3>
            <p>A orillas de la majestuosa Laguna de Tota, el lago más grande de Colombia</p>
          </div>

          <div class="feature-card animate-fadeInUp" style="animation-delay: 0.1s">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
              </svg>
            </div>
            <h3>Confort de Lujo</h3>
            <p>Habitaciones elegantemente diseñadas con todas las comodidades modernas</p>
          </div>

          <div class="feature-card animate-fadeInUp" style="animation-delay: 0.2s">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                <path d="M2 17l10 5 10-5"></path>
                <path d="M2 12l10 5 10-5"></path>
              </svg>
            </div>
            <h3>Gastronomía Excepcional</h3>
            <p>Restaurante gourmet con ingredientes locales y vistas espectaculares</p>
          </div>

          <div class="feature-card animate-fadeInUp" style="animation-delay: 0.3s">
            <div class="feature-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                <line x1="9" y1="9" x2="9.01" y2="9"></line>
                <line x1="15" y1="9" x2="15.01" y2="9"></line>
              </svg>
            </div>
            <h3>Spa & Bienestar</h3>
            <p>Centro de relajación con tratamientos personalizados y terapias holísticas</p>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
      <div class="container">
        <div class="cta-content">
          <h2>¿Listo para tu escape perfecto?</h2>
          <p>Reserva ahora y obtén hasta 20% de descuento en tu primera estadía</p>
          <router-link to="/reservar" class="btn btn-accent btn-lg">
            Ver Disponibilidad
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </router-link>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <ProfessionalFooter />
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import LogoRocamonti from '../components/LogoRocamonti.vue'
import ProfessionalFooter from '../components/ProfessionalFooter.vue'

const router = useRouter()
</script>

<style scoped>
@import '../assets/styles/theme.css';

.home-page {
  width: 100%;
  min-height: 100vh;
}

/* ==================== HERO SECTION ==================== */
.hero {
  position: relative;
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
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
  background-attachment: fixed;
  z-index: -1;
}

.hero-overlay {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: var(--gradient-hero);
  z-index: 1;
}

/* Hero Navigation */
.hero-nav {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: var(--z-sticky);
  padding: var(--space-lg) 0;
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.05);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.hero-nav-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 var(--space-xl);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-xl);
}

.hero-nav-links {
  display: flex;
  align-items: center;
  gap: var(--space-2xl);
  flex: 1;
  justify-content: center;
}

.hero-link {
  color: var(--white);
  font-size: var(--text-base);
  font-weight: 500;
  padding: var(--space-sm) var(--space-md);
  border-radius: var(--radius-md);
  transition: all var(--transition-base) var(--ease);
  position: relative;
}

.hero-link:hover {
  background: rgba(255, 255, 255, 0.1);
}

.hero-link.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 50%;
  transform: translateX(-50%);
  width: 30px;
  height: 3px;
  background: var(--accent);
  border-radius: var(--radius-full);
}

/* Hero Content */
.hero-content {
  position: relative;
  z-index: 2;
  text-align: center;
  color: var(--white);
  max-width: 900px;
  padding: var(--space-3xl) var(--space-xl);
  margin-top: 80px;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: var(--space-sm);
  padding: var(--space-sm) var(--space-lg);
  background: rgba(245, 158, 11, 0.2);
  border: 1px solid var(--accent);
  color: var(--accent);
  border-radius: var(--radius-full);
  font-size: var(--text-sm);
  font-weight: 600;
  margin-bottom: var(--space-xl);
  backdrop-filter: blur(10px);
}

.hero-title {
  font-family: var(--font-display);
  font-size: var(--text-6xl);
  font-weight: 700;
  line-height: 1.1;
  margin-bottom: var(--space-lg);
  text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
}

.hero-subtitle {
  font-size: var(--text-2xl);
  font-weight: 500;
  margin-bottom: var(--space-lg);
  color: rgba(255, 255, 255, 0.95);
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
}

.hero-description {
  font-size: var(--text-lg);
  line-height: 1.7;
  color: rgba(255, 255, 255, 0.85);
  margin-bottom: var(--space-2xl);
  max-width: 700px;
  margin-left: auto;
  margin-right: auto;
}

.hero-actions {
  display: flex;
  gap: var(--space-lg);
  justify-content: center;
  flex-wrap: wrap;
  margin-bottom: var(--space-3xl);
}

.hero-stats {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--space-2xl);
  padding: var(--space-xl);
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  border-radius: var(--radius-lg);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat {
  text-align: center;
}

.stat-value {
  font-size: var(--text-4xl);
  font-weight: 700;
  color: var(--accent);
  margin-bottom: var(--space-xs);
}

.stat-label {
  font-size: var(--text-sm);
  color: rgba(255, 255, 255, 0.8);
}

.stat-divider {
  width: 1px;
  height: 50px;
  background: rgba(255, 255, 255, 0.3);
}

/* Scroll Indicator */
.scroll-indicator {
  position: absolute;
  bottom: var(--space-2xl);
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-md);
  color: var(--white);
  font-size: var(--text-sm);
  animation: float 3s ease-in-out infinite;
}

.scroll-mouse {
  width: 28px;
  height: 45px;
  border: 2px solid var(--white);
  border-radius: 20px;
  display: flex;
  justify-content: center;
  padding-top: 8px;
}

.scroll-wheel {
  width: 3px;
  height: 8px;
  background: var(--white);
  border-radius: 3px;
  animation: scroll 1.5s ease-in-out infinite;
}

@keyframes scroll {
  0%, 100% { transform: translateY(0); opacity: 1; }
  50% { transform: translateY(8px); opacity: 0.5; }
}

/* ==================== FEATURES SECTION ==================== */
.features-section {
  padding: var(--space-3xl) 0;
  background: var(--white);
}

.section-header {
  text-align: center;
  max-width: 700px;
  margin: 0 auto var(--space-3xl);
}

.section-badge {
  display: inline-block;
  padding: var(--space-sm) var(--space-lg);
  background: var(--gradient-primary);
  color: var(--white);
  border-radius: var(--radius-full);
  font-size: var(--text-sm);
  font-weight: 600;
  margin-bottom: var(--space-lg);
}

.section-header h2 {
  font-size: var(--text-4xl);
  margin-bottom: var(--space-md);
}

.section-header p {
  font-size: var(--text-lg);
  color: var(--gray-600);
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: var(--space-2xl);
}

.feature-card {
  padding: var(--space-2xl);
  text-align: center;
  background: var(--white);
  border-radius: var(--radius-lg);
  border: 1px solid var(--gray-200);
  transition: all var(--transition-base) var(--ease);
}

.feature-card:hover {
  transform: translateY(-8px);
  box-shadow: var(--shadow-lg);
  border-color: var(--primary-light);
}

.feature-icon {
  width: 80px;
  height: 80px;
  margin: 0 auto var(--space-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--gradient-primary);
  color: var(--white);
  border-radius: var(--radius-lg);
  box-shadow: var(--shadow-primary);
}

.feature-card h3 {
  font-size: var(--text-xl);
  margin-bottom: var(--space-md);
  color: var(--gray-900);
}

.feature-card p {
  font-size: var(--text-base);
  color: var(--gray-600);
  line-height: 1.6;
  margin: 0;
}

/* ==================== CTA SECTION ==================== */
.cta-section {
  padding: var(--space-3xl) 0;
  background: var(--gradient-primary);
  color: var(--white);
}

.cta-content {
  text-align: center;
  max-width: 800px;
  margin: 0 auto;
}

.cta-content h2 {
  font-size: var(--text-4xl);
  margin-bottom: var(--space-lg);
  color: var(--white);
}

.cta-content p {
  font-size: var(--text-xl);
  margin-bottom: var(--space-2xl);
  color: rgba(255, 255, 255, 0.9);
}

/* ==================== RESPONSIVE ==================== */
.desktop-only {
  display: flex;
}

@media (max-width: 1024px) {
  .hero-title {
    font-size: var(--text-5xl);
  }

  .features-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .desktop-only {
    display: none;
  }

  .hero-title {
    font-size: var(--text-4xl);
  }

  .hero-subtitle {
    font-size: var(--text-xl);
  }

  .hero-description {
    font-size: var(--text-base);
  }

  .hero-actions {
    flex-direction: column;
  }

  .hero-stats {
    flex-direction: column;
    gap: var(--space-lg);
  }

  .stat-divider {
    width: 100%;
    height: 1px;
  }

  .features-grid {
    grid-template-columns: 1fr;
  }

  .cta-content h2 {
    font-size: var(--text-3xl);
  }

  .cta-content p {
    font-size: var(--text-lg);
  }
}
</style>
