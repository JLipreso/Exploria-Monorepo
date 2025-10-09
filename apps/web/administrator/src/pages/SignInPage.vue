<template>
  <div class="signin-container">
    <div class="signin-card">
      <!-- Logo and Header -->
      <div class="signin-header">
        <div class="logo-container">
          <img src="/logo-with-text-dark-green-mini.png" alt="Exploria" class="logo" />
        </div>
        <h1 class="portal-title">Administrator Portal</h1>
        <p class="portal-subtitle">Sign in to manage the Exploria platform</p>
      </div>

      <!-- Error Alert -->
      <div v-if="errorMessage" class="alert alert-danger" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ errorMessage }}
      </div>

      <!-- Success Alert -->
      <div v-if="successMessage" class="alert alert-success" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ successMessage }}
      </div>

      <!-- Sign In Form -->
      <form @submit.prevent="handleSignIn" class="signin-form">
        <div class="mb-3">
          <label for="email" class="form-label">
            <i class="bi bi-envelope me-2"></i>Email Address
          </label>
          <input
            type="email"
            class="form-control"
            id="email"
            v-model="email"
            placeholder="Enter your email"
            required
            :disabled="loading"
          />
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">
            <i class="bi bi-lock me-2"></i>Password
          </label>
          <div class="input-group">
            <input
              :type="showPassword ? 'text' : 'password'"
              class="form-control"
              id="password"
              v-model="password"
              placeholder="Enter your password"
              required
              :disabled="loading"
            />
            <button
              class="btn btn-outline-secondary"
              type="button"
              @click="showPassword = !showPassword"
              :disabled="loading"
            >
              <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
            </button>
          </div>
        </div>

        <div class="mb-3 form-check">
          <input
            type="checkbox"
            class="form-check-input"
            id="remember"
            v-model="rememberMe"
            :disabled="loading"
          />
          <label class="form-check-label" for="remember">
            Remember me
          </label>
        </div>

        <button
          type="submit"
          class="btn btn-primary btn-signin w-100"
          :disabled="loading"
        >
          <span v-if="loading">
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Signing in...
          </span>
          <span v-else>
            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
          </span>
        </button>
      </form>

      <!-- Divider -->
      <div class="divider my-4">
        <span>OR</span>
      </div>

      <!-- Google Sign In -->
      <button
        type="button"
        class="btn btn-google w-100"
        @click="handleGoogleSignIn"
        :disabled="loading"
      >
        <img src="/icons8-google-48.png" alt="Google" class="google-icon" />
        Sign in with Google
      </button>

      <!-- Forgot Password Link -->
      <div class="text-center mt-4">
        <a href="#" class="forgot-password-link" @click.prevent="handleForgotPassword">
          Forgot your password?
        </a>
      </div>

      <!-- Footer -->
      <div class="signin-footer mt-4">
        <p class="text-muted small text-center">
          &copy; {{ currentYear }} Exploria Travel and Tours. All rights reserved.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { signInWithEmailAndPassword, signInWithPopup, GoogleAuthProvider } from 'firebase/auth';
import { auth } from '../assets/ts/firebase';
import { authPortalServices } from '@exploria/shared-core';

const router = useRouter();

// Form data
const email = ref('');
const password = ref('');
const showPassword = ref(false);
const rememberMe = ref(false);

// UI state
const loading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

// Get current year for footer
const currentYear = computed(() => new Date().getFullYear());

/**
 * Handle email/password sign in
 */
async function handleSignIn() {
  errorMessage.value = '';
  successMessage.value = '';
  loading.value = true;

  try {
    // Authenticate with Firebase
    const userCredential = await signInWithEmailAndPassword(auth, email.value, password.value);
    const firebaseUser = userCredential.user;

    // Get device info and IP
    const deviceInfo = authPortalServices.getDeviceInfo();
    const ipAddress = await authPortalServices.getUserIpAddress();

    // Login to backend portal
    const response = await authPortalServices.adminLogin({
      email: firebaseUser.email!,
      firebase_uid: firebaseUser.uid,
      device_info: deviceInfo,
      ip_address: ipAddress,
      user_agent: navigator.userAgent
    });

    if (response.success && response.data) {
      // Store session
      authPortalServices.storePortalSession(response.data, 'admin');
      
      successMessage.value = 'Sign in successful! Redirecting...';
      
      // Redirect to dashboard
      setTimeout(() => {
        router.push('/admin/dashboard');
      }, 1500);
    } else {
      errorMessage.value = response.message || 'Access denied. Administrator privileges required.';
      
      // Sign out from Firebase if backend login failed
      await auth.signOut();
    }
  } catch (error: any) {
    console.error('Sign in error:', error);
    
    // Handle specific Firebase errors
    if (error.code === 'auth/user-not-found') {
      errorMessage.value = 'No account found with this email address.';
    } else if (error.code === 'auth/wrong-password') {
      errorMessage.value = 'Incorrect password. Please try again.';
    } else if (error.code === 'auth/invalid-email') {
      errorMessage.value = 'Invalid email address format.';
    } else if (error.code === 'auth/user-disabled') {
      errorMessage.value = 'This account has been disabled.';
    } else if (error.code === 'auth/too-many-requests') {
      errorMessage.value = 'Too many failed attempts. Please try again later.';
    } else {
      errorMessage.value = error.message || 'Sign in failed. Please try again.';
    }
  } finally {
    loading.value = false;
  }
}

/**
 * Handle Google sign in
 */
async function handleGoogleSignIn() {
  errorMessage.value = '';
  successMessage.value = '';
  loading.value = true;

  try {
    const provider = new GoogleAuthProvider();
    const userCredential = await signInWithPopup(auth, provider);
    const firebaseUser = userCredential.user;

    // Get device info and IP
    const deviceInfo = authPortalServices.getDeviceInfo();
    const ipAddress = await authPortalServices.getUserIpAddress();

    // Login to backend portal
    const response = await authPortalServices.adminLogin({
      email: firebaseUser.email!,
      firebase_uid: firebaseUser.uid,
      device_info: deviceInfo,
      ip_address: ipAddress,
      user_agent: navigator.userAgent
    });

    if (response.success && response.data) {
      // Store session
      authPortalServices.storePortalSession(response.data, 'admin');
      
      successMessage.value = 'Google sign in successful! Redirecting...';
      
      // Redirect to dashboard
      setTimeout(() => {
        router.push('/admin/dashboard');
      }, 1500);
    } else {
      errorMessage.value = response.message || 'Access denied. Administrator privileges required.';
      
      // Sign out from Firebase if backend login failed
      await auth.signOut();
    }
  } catch (error: any) {
    console.error('Google sign in error:', error);
    
    if (error.code === 'auth/popup-closed-by-user') {
      errorMessage.value = 'Sign in cancelled.';
    } else if (error.code === 'auth/popup-blocked') {
      errorMessage.value = 'Popup blocked. Please enable popups for this site.';
    } else {
      errorMessage.value = error.message || 'Google sign in failed.';
    }
  } finally {
    loading.value = false;
  }
}

/**
 * Handle forgot password
 */
function handleForgotPassword() {
  router.push('/admin/forgot-password');
}
</script>

<style scoped>
.signin-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #243a2d 0%, #1a2820 100%);
  padding: 20px;
}

.signin-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  padding: 40px;
  width: 100%;
  max-width: 480px;
}

.signin-header {
  text-align: center;
  margin-bottom: 32px;
}

.logo-container {
  margin-bottom: 20px;
}

.logo {
  width: 120px;
  height: auto;
}

.portal-title {
  color: #243a2d;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 8px;
}

.portal-subtitle {
  color: #6c757d;
  font-size: 16px;
  margin: 0;
}

.signin-form {
  margin-top: 24px;
}

.form-label {
  color: #243a2d;
  font-weight: 600;
  font-size: 14px;
}

.form-control {
  border-radius: 8px;
  border: 2px solid #e9ecef;
  padding: 12px 16px;
  font-size: 15px;
  transition: all 0.3s ease;
}

.form-control:focus {
  border-color: #243a2d;
  box-shadow: 0 0 0 0.2rem rgba(36, 58, 45, 0.15);
}

.input-group .btn-outline-secondary {
  border-radius: 0 8px 8px 0;
  border: 2px solid #e9ecef;
  border-left: none;
}

.input-group .form-control {
  border-radius: 8px 0 0 8px;
}

.btn-signin {
  background-color: #243a2d;
  border: none;
  border-radius: 8px;
  padding: 14px;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-signin:hover:not(:disabled) {
  background-color: #1a2820;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(36, 58, 45, 0.3);
}

.btn-signin:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}

.divider {
  position: relative;
  text-align: center;
}

.divider::before,
.divider::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 42%;
  height: 1px;
  background: #dee2e6;
}

.divider::before {
  left: 0;
}

.divider::after {
  right: 0;
}

.divider span {
  background: white;
  padding: 0 16px;
  color: #6c757d;
  font-size: 14px;
  font-weight: 600;
}

.btn-google {
  background: white;
  border: 2px solid #e9ecef;
  border-radius: 8px;
  padding: 12px;
  font-size: 16px;
  font-weight: 600;
  color: #495057;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
}

.btn-google:hover:not(:disabled) {
  background: #f8f9fa;
  border-color: #243a2d;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.google-icon {
  width: 24px;
  height: 24px;
}

.forgot-password-link {
  color: #243a2d;
  font-weight: 600;
  text-decoration: none;
  font-size: 14px;
  transition: color 0.3s ease;
}

.forgot-password-link:hover {
  color: #1a2820;
  text-decoration: underline;
}

.signin-footer {
  border-top: 1px solid #e9ecef;
  padding-top: 20px;
}

.alert {
  border-radius: 8px;
  padding: 12px 16px;
  margin-bottom: 20px;
}

@media (max-width: 576px) {
  .signin-card {
    padding: 24px;
  }

  .portal-title {
    font-size: 24px;
  }

  .portal-subtitle {
    font-size: 14px;
  }
}
</style>