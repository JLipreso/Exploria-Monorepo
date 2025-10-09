<template>
  <div class="signin-container">
    <div class="signin-card">
      <!-- Logo and Header -->
      <div class="signin-header">
        <div class="logo-container">
          <img src="../assets/img/logo-with-text-dark-green-mini.png" alt="Exploria" class="logo" />
        </div>
        <h1 class="portal-title">Staff Portal</h1>
        <p class="portal-subtitle">Sign in to access staff tools</p>
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
        <img src="../assets/img/icons8-google-48.png" alt="Google" class="google-icon" />
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
    const response = await authPortalServices.staffLogin({
      email: firebaseUser.email!,
      firebase_uid: firebaseUser.uid,
      device_info: deviceInfo,
      ip_address: ipAddress,
      user_agent: navigator.userAgent
    });

    if (response.success && response.data) {
      // Store session
      authPortalServices.storePortalSession(response.data, 'staff');
      
      successMessage.value = 'Sign in successful! Redirecting...';
      
      // Redirect to dashboard
      setTimeout(() => {
        router.push('/staff/dashboard');
      }, 1500);
    } else {
      errorMessage.value = response.message || 'Access denied. Staff privileges required.';
      
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
    const response = await authPortalServices.staffLogin({
      email: firebaseUser.email!,
      firebase_uid: firebaseUser.uid,
      device_info: deviceInfo,
      ip_address: ipAddress,
      user_agent: navigator.userAgent
    });

    if (response.success && response.data) {
      // Store session
      authPortalServices.storePortalSession(response.data, 'staff');
      
      successMessage.value = 'Google sign in successful! Redirecting...';
      
      // Redirect to dashboard
      setTimeout(() => {
        router.push('/staff/dashboard');
      }, 1500);
    } else {
      errorMessage.value = response.message || 'Access denied. Staff privileges required.';
      
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
  router.push('/staff/forgot-password');
}

</script>

<style scoped>
  @import "../../../assets/css/signin.css";
</style>

