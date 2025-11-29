<template>
  <div class="login-admin-page">
    <div class="login-container">
      <div class="login-card">
        <div class="logo-section">
          <h1>🏨 Hotel Rocamonti</h1>
          <p>Panel de Administración</p>
        </div>

        <form @submit.prevent="handleLogin" class="login-form">
          <div class="form-group">
            <label for="username">Usuario</label>
            <input
              id="username"
              v-model="credentials.username"
              type="text"
              placeholder="Ingresa tu usuario"
              required
              autocomplete="username"
            />
          </div>

          <div class="form-group">
            <label for="password">Contraseña</label>
            <input
              id="password"
              v-model="credentials.password"
              type="password"
              placeholder="Ingresa tu contraseña"
              required
              autocomplete="current-password"
            />
          </div>

          <div v-if="error" class="error-message">
            {{ error }}
          </div>

          <button type="submit" class="btn-login" :disabled="loading">
            {{ loading ? 'Ingresando...' : 'Ingresar' }}
          </button>
        </form>

        <div class="help-text">
          <p>Credenciales por defecto:</p>
          <p><strong>Usuario:</strong> admin</p>
          <p><strong>Contraseña:</strong> admin123</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const credentials = ref({
  username: '',
  password: ''
})

const loading = ref(false)
const error = ref(null)

const handleLogin = async () => {
  loading.value = true
  error.value = null

  try {
    // Validación simple (puedes cambiarla por una llamada a API)
    if (credentials.value.username === 'admin' && credentials.value.password === 'admin123') {
      // Guardar sesión
      localStorage.setItem('admin_token', 'admin-session-' + Date.now())
      localStorage.setItem('admin_user', credentials.value.username)

      // Redirigir al dashboard
      router.push('/admin/dashboard')
    } else {
      error.value = 'Usuario o contraseña incorrectos'
    }
  } catch (err) {
    error.value = 'Error al iniciar sesión'
    console.error(err)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-admin-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem;
}

.login-container {
  width: 100%;
  max-width: 400px;
}

.login-card {
  background: white;
  border-radius: 12px;
  padding: 2.5rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.logo-section {
  text-align: center;
  margin-bottom: 2rem;
}

.logo-section h1 {
  font-size: 2rem;
  color: #667eea;
  margin-bottom: 0.5rem;
}

.logo-section p {
  color: #6b7280;
  font-size: 0.95rem;
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.form-group input {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.form-group input:focus {
  outline: none;
  border-color: #667eea;
}

.error-message {
  background: #fee2e2;
  color: #991b1b;
  padding: 0.75rem;
  border-radius: 8px;
  font-size: 0.9rem;
  text-align: center;
}

.btn-login {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 1rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
}

.btn-login:hover:not(:disabled) {
  transform: translateY(-2px);
}

.btn-login:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.help-text {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
  text-align: center;
  color: #6b7280;
  font-size: 0.85rem;
}

.help-text strong {
  color: #374151;
}
</style>
