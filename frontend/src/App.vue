<script setup>
import { RouterView, useRoute } from 'vue-router'
import ProfessionalNavbar from './components/ProfessionalNavbar.vue'
import ProfessionalFooter from './components/ProfessionalFooter.vue'
import { computed } from 'vue'

const route = useRoute()

// Mostrar navbar y footer solo en páginas que no sean home ni admin
const showLayout = computed(() => {
  return !route.path.startsWith('/admin') && route.path !== '/'
})
</script>

<template>
  <div id="app">
    <ProfessionalNavbar v-if="showLayout" />

    <main>
      <RouterView />
    </main>

    <ProfessionalFooter v-if="showLayout" />
  </div>
</template>

<style>
/* Los estilos base ahora están en theme.css */
#app {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

main {
  flex: 1;
}
</style>
