<template>
  <nav class="professional-navbar" :class="{ 'navbar-scrolled': isScrolled }">
    <div class="navbar-container">
      <!-- Logo -->
      <router-link to="/" class="navbar-logo">
        <LogoRocamonti variant="light" width="180px" height="54px" :show-stars="false" />
      </router-link>

      <!-- Desktop Navigation -->
      <div class="navbar-links desktop-only">
        <router-link to="/" class="nav-link" exact-active-class="active">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
            <polyline points="9 22 9 12 15 12 15 22"></polyline>
          </svg>
          Inicio
        </router-link>

        <router-link to="/reservar" class="nav-link" exact-active-class="active">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
          Reservar
        </router-link>

        <router-link to="/habitaciones" class="nav-link" exact-active-class="active">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M2 20h20"></path>
            <path d="M7 15h10"></path>
            <path d="M7 10h10"></path>
            <path d="M7 5h10"></path>
            <path d="M2 5h2"></path>
            <path d="M2 10h2"></path>
            <path d="M2 15h2"></path>
            <path d="M20 5h2"></path>
            <path d="M20 10h2"></path>
            <path d="M20 15h2"></path>
          </svg>
          Habitaciones
        </router-link>

        <router-link to="/contacto" class="nav-link" exact-active-class="active">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
          Contacto
        </router-link>
      </div>

      <!-- Actions -->
      <div class="navbar-actions">
        <!-- Botón Reservar -->
        <router-link to="/reservar" class="btn btn-primary btn-sm desktop-only">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
          Reservar Ahora
        </router-link>

        <!-- Mobile Menu Toggle -->
        <button @click="toggleMobileMenu" class="mobile-menu-toggle mobile-only" aria-label="Menú">
          <svg v-if="!isMobileMenuOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="12" x2="21" y2="12"></line>
            <line x1="3" y1="6" x2="21" y2="6"></line>
            <line x1="3" y1="18" x2="21" y2="18"></line>
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <transition name="slide-down">
      <div v-if="isMobileMenuOpen" class="mobile-menu mobile-only">
        <router-link to="/" class="mobile-nav-link" @click="closeMobileMenu">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          </svg>
          Inicio
        </router-link>

        <router-link to="/reservar" class="mobile-nav-link" @click="closeMobileMenu">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          </svg>
          Reservar
        </router-link>

        <router-link to="/habitaciones" class="mobile-nav-link" @click="closeMobileMenu">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M2 20h20"></path>
          </svg>
          Habitaciones
        </router-link>

        <router-link to="/contacto" class="mobile-nav-link" @click="closeMobileMenu">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
          Contacto
        </router-link>
      </div>
    </transition>
  </nav>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import LogoRocamonti from './LogoRocamonti.vue'

const isScrolled = ref(false)
const isMobileMenuOpen = ref(false)

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50
}

const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value
  // Prevenir scroll cuando el menú está abierto
  if (isMobileMenuOpen.value) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
}

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false
  document.body.style.overflow = ''
}

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  document.body.style.overflow = ''
})
</script>

<style scoped>
@import '../assets/styles/theme.css';

.professional-navbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: var(--z-sticky);
  background: var(--white);
  box-shadow: var(--shadow-sm);
  transition: all var(--transition-base) var(--ease);
}

.navbar-scrolled {
  box-shadow: var(--shadow-md);
}

.navbar-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: var(--space-lg) var(--space-xl);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--space-xl);
}

.navbar-scrolled .navbar-container {
  padding: var(--space-md) var(--space-xl);
}

/* Logo */
.navbar-logo {
  flex-shrink: 0;
  transition: transform var(--transition-base) var(--ease);
}

.navbar-logo:hover {
  transform: scale(1.05);
}

/* Desktop Links */
.navbar-links {
  display: flex;
  align-items: center;
  gap: var(--space-xl);
  flex: 1;
  justify-content: center;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  padding: var(--space-sm) var(--space-md);
  font-size: var(--text-base);
  font-weight: 500;
  color: var(--gray-700);
  border-radius: var(--radius-md);
  transition: all var(--transition-base) var(--ease);
  position: relative;
}

.nav-link svg {
  transition: transform var(--transition-base) var(--ease);
}

.nav-link:hover {
  color: var(--primary);
  background: var(--gray-50);
}

.nav-link:hover svg {
  transform: translateY(-2px);
}

.nav-link.active {
  color: var(--primary);
  font-weight: 600;
}

.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 50%;
  transform: translateX(-50%);
  width: 30px;
  height: 3px;
  background: var(--gradient-primary);
  border-radius: var(--radius-full);
}

/* Actions */
.navbar-actions {
  display: flex;
  align-items: center;
  gap: var(--space-lg);
}

.action-phone {
  display: flex;
  align-items: center;
  gap: var(--space-sm);
  padding: var(--space-sm) var(--space-md);
  font-size: var(--text-sm);
  font-weight: 600;
  color: var(--gray-700);
  background: var(--gray-50);
  border-radius: var(--radius-md);
  transition: all var(--transition-base) var(--ease);
}

.action-phone:hover {
  color: var(--primary);
  background: var(--gray-100);
  transform: translateY(-2px);
}

.action-phone svg {
  color: var(--primary);
}

/* Mobile Menu Toggle */
.mobile-menu-toggle {
  display: none;
  padding: var(--space-sm);
  background: none;
  border: none;
  color: var(--gray-700);
  cursor: pointer;
  border-radius: var(--radius-md);
  transition: all var(--transition-base) var(--ease);
}

.mobile-menu-toggle:hover {
  background: var(--gray-100);
  color: var(--primary);
}

/* Mobile Menu */
.mobile-menu {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: var(--white);
  box-shadow: var(--shadow-lg);
  padding: var(--space-lg);
  display: flex;
  flex-direction: column;
  gap: var(--space-sm);
  max-height: calc(100vh - 80px);
  overflow-y: auto;
}

.mobile-nav-link {
  display: flex;
  align-items: center;
  gap: var(--space-md);
  padding: var(--space-lg);
  font-size: var(--text-lg);
  font-weight: 500;
  color: var(--gray-700);
  background: var(--gray-50);
  border-radius: var(--radius-lg);
  transition: all var(--transition-base) var(--ease);
}

.mobile-nav-link:hover,
.mobile-nav-link:active {
  color: var(--primary);
  background: var(--gray-100);
  transform: translateX(4px);
}

.mobile-nav-link svg {
  color: var(--primary);
}

.mobile-phone {
  background: var(--gradient-primary);
  color: var(--white);
  font-weight: 600;
}

.mobile-phone:hover {
  transform: translateX(0) translateY(-2px);
  box-shadow: var(--shadow-primary);
}

.mobile-phone svg {
  color: var(--white);
}

/* Transitions */
.slide-down-enter-active,
.slide-down-leave-active {
  transition: all var(--transition-base) var(--ease);
}

.slide-down-enter-from {
  opacity: 0;
  transform: translateY(-20px);
}

.slide-down-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

/* Responsive */
.desktop-only {
  display: flex;
}

.mobile-only {
  display: none;
}

@media (max-width: 1024px) {
  .navbar-links {
    gap: var(--space-lg);
  }

  .nav-link {
    font-size: var(--text-sm);
  }
}

@media (max-width: 768px) {
  .desktop-only {
    display: none !important;
  }

  .mobile-only {
    display: flex;
  }

  .navbar-container {
    padding: var(--space-md) var(--space-lg);
  }

  .navbar-actions {
    gap: var(--space-md);
  }

  .navbar-logo {
    max-width: 150px;
  }
}
</style>
