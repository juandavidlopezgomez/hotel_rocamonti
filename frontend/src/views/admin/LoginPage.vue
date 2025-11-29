<template>
  <div class="login-page">
    <div class="login-card">
      <h1 class="title">Admin Login</h1>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" v-model="email" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" v-model="password" required>
        </div>
        <div v-if="error" class="error-message">{{ error }}</div>
        <button type="submit" class="btn-login" :disabled="loading">
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../services/api';

const email = ref('');
const password = ref('');
const error = ref(null);
const loading = ref(false);
const router = useRouter();

async function handleLogin() {
  loading.value = true;
  error.value = null;
  try {
    const response = await api.post('/admin/login', {
      email: email.value,
      password: password.value,
    });

    if (response.data.token) {
      localStorage.setItem('admin-token', response.data.token);
      // Redirect to admin dashboard
      router.push('/admin/dashboard');
    } else {
      throw new Error('Login failed: No token received.');
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'An error occurred during login.';
    console.error('Login error:', err);
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
.login-page {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f0f2f5;
}
.login-card {
  background: white;
  padding: 3rem;
  border-radius: 12px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  width: 100%;
  max-width: 400px;
}
.title {
  text-align: center;
  font-size: 2rem;
  color: #333;
  margin-bottom: 2rem;
}
.form-group {
  margin-bottom: 1.5rem;
}
.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #555;
}
.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 1rem;
}
.error-message {
  color: #ef4444;
  margin-bottom: 1rem;
  text-align: center;
}
.btn-login {
  width: 100%;
  padding: 1rem;
  background: #0071c2;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}
.btn-login:disabled {
  background: #999;
  cursor: not-allowed;
}
.btn-login:hover:not(:disabled) {
  background: #005a9c;
}
</style>
