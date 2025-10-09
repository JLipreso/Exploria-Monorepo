# Firebase Integration Guide for Exploria Travel and Tours
## Complete Step-by-Step Setup for First-Time Firebase Users

---

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Create Firebase Project](#create-firebase-project)
3. [Configure Firebase Authentication](#configure-firebase-authentication)
4. [Get Firebase Configuration](#get-firebase-configuration)
5. [Install Firebase SDK](#install-firebase-sdk)
6. [Configure Backend (Laravel)](#configure-backend-laravel)
7. [Configure Frontend (Vue 3)](#configure-frontend-vue-3)
8. [Test Firebase Integration](#test-firebase-integration)
9. [Security Best Practices](#security-best-practices)
10. [Troubleshooting](#troubleshooting)

---

## Prerequisites

Before starting, ensure you have:
- ✅ A Google account
- ✅ Node.js installed (v20.19.0 or higher)
- ✅ PHP 8.1+ and Composer installed
- ✅ Laravel 10 project setup
- ✅ Vue 3 project setup
- ✅ Database (MySQL/PostgreSQL) configured

---

## Step 1: Create Firebase Project

### 1.1 Go to Firebase Console
1. Open your browser and visit: **https://console.firebase.google.com/**
2. Click **"Go to console"** (top right)
3. Sign in with your Google account

### 1.2 Create New Project
1. Click **"Add project"** or **"Create a project"**
2. Enter project name: **"Exploria Travel Tours"** (or your preferred name)
3. Click **"Continue"**

### 1.3 Configure Google Analytics (Optional)
1. You can enable or disable Google Analytics
   - **Recommended**: Disable for development, enable for production
2. If enabling:
   - Select or create a Google Analytics account
   - Accept terms and click **"Create project"**
3. Wait for project creation (30-60 seconds)
4. Click **"Continue"** when ready

---

## Step 2: Configure Firebase Authentication

### 2.1 Access Authentication Settings
1. In Firebase Console, select your project **"Exploria Travel Tours"**
2. In the left sidebar, click **"Build"** → **"Authentication"**
3. Click **"Get started"** button

### 2.2 Enable Sign-in Methods
#### Enable Email/Password Authentication:
1. Click on **"Sign-in method"** tab
2. Click on **"Email/Password"** provider
3. Toggle **"Enable"** switch ON
4. ✅ Check **"Email/Password"**
5. ❌ Leave **"Email link (passwordless sign-in)"** OFF (for now)
6. Click **"Save"**

#### Enable Google Sign-In (Recommended):
1. Click **"Add new provider"**
2. Select **"Google"**
3. Toggle **"Enable"** switch ON
4. Enter **Project support email**: Your email address
5. Click **"Save"**

#### Optional: Enable Other Providers
- Facebook: Requires Facebook App ID and Secret
- Apple: Requires Apple Developer account
- Phone: Requires phone authentication setup

### 2.3 Configure Authorized Domains
1. Still in **"Sign-in method"** tab
2. Scroll down to **"Authorized domains"**
3. You'll see **localhost** already added (for development)
4. For production, click **"Add domain"**
   - Add your domain: e.g., **exploria.com**
   - Add subdomain if needed: e.g., **app.exploria.com**
5. Click **"Add"**

---

## Step 3: Get Firebase Configuration

### 3.1 Add Web App to Firebase Project
1. In Firebase Console, click the **gear icon** (⚙️) next to "Project Overview"
2. Select **"Project settings"**
3. Scroll down to **"Your apps"** section
4. Click the **Web icon** `</>`  (looks like HTML brackets)
5. Register your app:
   - **App nickname**: "Exploria Web App"
   - ✅ Check **"Also set up Firebase Hosting"** (optional)
   - Click **"Register app"**

### 3.2 Copy Firebase Configuration
You'll see a configuration object like this:

```javascript
const firebaseConfig = {
  apiKey: "AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
  authDomain: "exploria-travel-tours.firebaseapp.com",
  projectId: "exploria-travel-tours",
  storageBucket: "exploria-travel-tours.appspot.com",
  messagingSenderId: "123456789012",
  appId: "1:123456789012:web:abcdef1234567890abcdef"
};
```

**⚠️ IMPORTANT**: Save this configuration - you'll need it!

### 3.3 Click "Continue to console"

---

## Step 4: Install Firebase SDK

### 4.1 Install Firebase in Your Monorepo
Open terminal in your project root and run:

```bash
# If not already installed (check your package.json)
npm install firebase

# Verify installation
npm list firebase
```

Expected output: `firebase@12.3.0` (or latest version)

---

## Step 5: Configure Backend (Laravel)

### 5.1 Install Required PHP Packages
```bash
cd backend/laravel
composer require firebase/php-jwt
composer require kreait/firebase-php
```

### 5.2 Add Firebase Admin SDK Configuration
1. Download Firebase Admin SDK credentials:
   - In Firebase Console → Project Settings (⚙️)
   - Click **"Service accounts"** tab
   - Click **"Generate new private key"**
   - Click **"Generate key"** (downloads JSON file)
   - **⚠️ Keep this file secure!**

2. Save the JSON file:
   - Rename to: `firebase-credentials.json`
   - Place in: `backend/laravel/storage/app/firebase/`
   - Add to `.gitignore`:
     ```
     storage/app/firebase/firebase-credentials.json
     ```

### 5.3 Add Firebase Configuration to .env
Add to `backend/laravel/.env`:

```env
FIREBASE_PROJECT_ID=exploria-travel-tours
FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
```

### 5.4 Set up CORS Configuration
Edit `backend/laravel/config/cors.php`:

```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'http://localhost:3000',
        'http://localhost:5173',
        'https://your-production-domain.com'
    ],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
```

### 5.5 Upload Database Tables
1. Copy the SQL files to your project:
   - `users_table.sql`
   - `auth_history_table.sql`

2. Execute in phpMyAdmin or command line:
```bash
# Using MySQL command line
mysql -u your_username -p your_database < users_table.sql
mysql -u your_username -p your_database < auth_history_table.sql
```

Or in phpMyAdmin:
- Open phpMyAdmin
- Select your database
- Click "Import" tab
- Choose file and import

### 5.6 Add Controller and Routes
1. Create controller directory:
```bash
mkdir -p app/Http/Controllers/Users
```

2. Copy `AuthFirebaseController.php` to:
   `backend/laravel/app/Http/Controllers/Users/AuthFirebaseController.php`

3. Add routes to `backend/laravel/routes/api.php`:
```php
// Copy contents from firebase_auth_routes.php
```

### 5.7 Test Backend Setup
```bash
# Start Laravel server
php artisan serve

# In another terminal, test API endpoint
curl -X POST http://localhost:8000/api/auth/firebase/check-email \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}'
```

Expected response:
```json
{
  "success": true,
  "message": "Email is available",
  "data": {
    "exists": false
  }
}
```

---

## Step 6: Configure Frontend (Vue 3)

### 6.1 Update Firebase Configuration
Edit `packages/shared-core/shared-services/firebase.ts`:

Replace the firebaseConfig object with YOUR actual Firebase config from Step 3.2:

```typescript
const firebaseConfig = {
  apiKey: "YOUR_ACTUAL_API_KEY",
  authDomain: "YOUR_ACTUAL_AUTH_DOMAIN",
  projectId: "YOUR_ACTUAL_PROJECT_ID",
  storageBucket: "YOUR_ACTUAL_STORAGE_BUCKET",
  messagingSenderId: "YOUR_ACTUAL_SENDER_ID",
  appId: "YOUR_ACTUAL_APP_ID"
};
```

### 6.2 Create Environment Variables
Create/Edit `.env` files in your Vue apps:

**For apps/web/.env.development:**
```env
VITE_API_URL=http://localhost:8000
VITE_APP_VERSION=1.0.0
VITE_FIREBASE_API_KEY=YOUR_ACTUAL_API_KEY
VITE_FIREBASE_AUTH_DOMAIN=YOUR_ACTUAL_AUTH_DOMAIN
VITE_FIREBASE_PROJECT_ID=YOUR_ACTUAL_PROJECT_ID
VITE_FIREBASE_STORAGE_BUCKET=YOUR_ACTUAL_STORAGE_BUCKET
VITE_FIREBASE_MESSAGING_SENDER_ID=YOUR_ACTUAL_SENDER_ID
VITE_FIREBASE_APP_ID=YOUR_ACTUAL_APP_ID
```

**For production (.env.production):**
```env
VITE_API_URL=https://api.yourproductiondomain.com
VITE_APP_VERSION=1.0.0
# ... same Firebase config
```

### 6.3 Update firebase.ts to Use Environment Variables
```typescript
const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId: import.meta.env.VITE_FIREBASE_APP_ID
};
```

### 6.4 Copy AuthFirebaseServices.ts
Copy `AuthFirebaseServices.ts` to:
`packages/shared-core/shared-services/AuthFirebaseServices.ts`

### 6.5 Create Sample Login Component
Create `apps/web/src/components/LoginForm.vue`:

```vue
<script setup lang="ts">
import { ref } from 'vue';
import { signInWithEmail, signInWithGoogle } from '@exploria/shared-core/shared-services/firebase';
import { loginUser, registerUser, getDeviceInfo } from '@exploria/shared-core/shared-services/AuthFirebaseServices';

const email = ref('');
const password = ref('');
const error = ref('');
const loading = ref(false);

async function handleEmailLogin() {
  loading.value = true;
  error.value = '';
  
  try {
    // Step 1: Authenticate with Firebase
    const userCredential = await signInWithEmail(email.value, password.value);
    const firebaseUser = userCredential.user;
    
    // Step 2: Login/Register in backend
    const deviceInfo = getDeviceInfo();
    const response = await loginUser({
      firebase_uid: firebaseUser.uid,
      email: firebaseUser.email!,
      auth_method: 'email_password',
      device_info: deviceInfo
    });
    
    if (response.success) {
      console.log('Login successful:', response.data);
      // Redirect to dashboard or home
    } else {
      error.value = response.message;
    }
  } catch (err: any) {
    error.value = err.message || 'Login failed';
    console.error('Login error:', err);
  } finally {
    loading.value = false;
  }
}

async function handleGoogleLogin() {
  loading.value = true;
  error.value = '';
  
  try {
    // Step 1: Authenticate with Google/Firebase
    const userCredential = await signInWithGoogle();
    const firebaseUser = userCredential.user;
    
    // Step 2: Check if user exists in backend
    const deviceInfo = getDeviceInfo();
    let response = await loginUser({
      firebase_uid: firebaseUser.uid,
      email: firebaseUser.email!,
      auth_method: 'google',
      device_info: deviceInfo
    });
    
    // If user doesn't exist, register them
    if (!response.success && response.message.includes('not found')) {
      response = await registerUser({
        firebase_uid: firebaseUser.uid,
        email: firebaseUser.email!,
        display_name: firebaseUser.displayName || '',
        profile_photo_url: firebaseUser.photoURL || '',
        auth_method: 'google',
        device_info: deviceInfo
      });
      
      if (response.success && response.data?.requires_profile_completion) {
        // Redirect to profile completion page
        console.log('Please complete your profile');
      }
    }
    
    if (response.success) {
      console.log('Login successful:', response.data);
      // Redirect to dashboard or home
    }
  } catch (err: any) {
    error.value = err.message || 'Google login failed';
    console.error('Google login error:', err);
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="login-form">
    <h2>Sign In to Exploria</h2>
    
    <div v-if="error" class="alert alert-danger">{{ error }}</div>
    
    <form @submit.prevent="handleEmailLogin">
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input 
          type="email" 
          class="form-control" 
          id="email" 
          v-model="email" 
          required
        />
      </div>
      
      <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input 
          type="password" 
          class="form-control" 
          id="password" 
          v-model="password" 
          required
        />
      </div>
      
      <button type="submit" class="btn btn-primary w-100" :disabled="loading">
        {{ loading ? 'Signing in...' : 'Sign In' }}
      </button>
    </form>
    
    <div class="divider">OR</div>
    
    <button 
      type="button" 
      class="btn btn-outline-secondary w-100" 
      @click="handleGoogleLogin"
      :disabled="loading"
    >
      <img src="/google-icon.svg" alt="Google" class="me-2" width="20" />
      Sign in with Google
    </button>
  </div>
</template>

<style scoped>
.login-form {
  max-width: 400px;
  margin: 0 auto;
  padding: 2rem;
}

.divider {
  text-align: center;
  margin: 1.5rem 0;
  position: relative;
}

.divider::before,
.divider::after {
  content: '';
  position: absolute;
  top: 50%;
  width: 45%;
  height: 1px;
  background: #dee2e6;
}

.divider::before {
  left: 0;
}

.divider::after {
  right: 0;
}
</style>
```

---

## Step 7: Test Firebase Integration

### 7.1 Start Development Servers
```bash
# Terminal 1: Start Laravel backend
cd backend/laravel
php artisan serve

# Terminal 2: Start Vue frontend
npm run dev:client
```

### 7.2 Test Authentication Flow

#### Test Email/Password Sign Up:
1. Open your browser: `http://localhost:5173`
2. Go to sign-up page
3. Enter email and password
4. Submit form
5. Check:
   - ✅ User created in Firebase Console (Authentication → Users)
   - ✅ User record in database `users` table
   - ✅ Login event in `auth_history` table

#### Test Google Sign-In:
1. Click "Sign in with Google" button
2. Select Google account
3. Authorize app
4. Check same items as above

### 7.3 Verify Database Records
```sql
-- Check users table
SELECT user_refid, email, confirmed, created_at FROM users ORDER BY created_at DESC LIMIT 5;

-- Check auth_history table
SELECT auth_refid, user_refid, auth_method, auth_status, auth_timestamp 
FROM auth_history ORDER BY auth_timestamp DESC LIMIT 10;
```

### 7.4 Test API Endpoints with Postman/cURL

**Check Email:**
```bash
curl -X POST http://localhost:8000/api/auth/firebase/check-email \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}'
```

**Register User:**
```bash
curl -X POST http://localhost:8000/api/auth/firebase/register \
  -H "Content-Type: application/json" \
  -d '{
    "firebase_uid": "test-firebase-uid-123",
    "email": "newuser@example.com",
    "display_name": "New User"
  }'
```

**Complete Profile:**
```bash
curl -X POST http://localhost:8000/api/auth/firebase/complete-profile \
  -H "Content-Type: application/json" \
  -d '{
    "user_refid": "USR-10042025033821-7SH",
    "firstname": "John",
    "lastname": "Doe",
    "birthday": "1990-01-15",
    "gender": "male"
  }'
```

---

## Step 8: Security Best Practices

### 8.1 Firebase Security Rules
In Firebase Console → Firestore Database → Rules:

```javascript
rules_version = '2';
service cloud.firestore {
  match /databases/{database}/documents {
    match /{document=**} {
      allow read, write: if request.auth != null;
    }
  }
}
```

### 8.2 Environment Variables Security
- ✅ Never commit `.env` files to Git
- ✅ Use different Firebase projects for dev/staging/production
- ✅ Rotate Firebase API keys regularly
- ✅ Implement API rate limiting in Laravel

### 8.3 Laravel Security Middleware
Add authentication middleware to routes:

```php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/api/auth/firebase/complete-profile', ...);
    Route::post('/api/auth/firebase/update-location', ...);
});
```

### 8.4 HTTPS in Production
- ✅ Always use HTTPS in production
- ✅ Enable Firebase App Check
- ✅ Configure Content Security Policy (CSP)

---

## Step 9: Troubleshooting

### Common Issues and Solutions

#### Issue 1: "Firebase: Error (auth/invalid-api-key)"
**Solution:**
- Verify `apiKey` in firebaseConfig is correct
- Check if API key is enabled in Google Cloud Console
- Ensure no extra spaces in .env file

#### Issue 2: "CORS policy error"
**Solution:**
- Add your frontend URL to Laravel CORS config
- Add domain to Firebase Authorized Domains
- Clear browser cache and restart servers

#### Issue 3: "User not found" after Firebase authentication
**Solution:**
- Ensure backend API is running
- Check API_BASE_URL in AuthFirebaseServices.ts
- Verify database connection in Laravel

#### Issue 4: Database connection fails
**Solution:**
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check Laravel .env database settings
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=exploria_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

#### Issue 5: Firebase initialization fails
**Solution:**
- Check if firebase package is installed: `npm list firebase`
- Clear node_modules and reinstall: `rm -rf node_modules && npm install`
- Verify import statements are correct

---

## Step 10: Next Steps

### Phase 2 Preparation
1. ✅ Test thoroughly with multiple users
2. ✅ Implement password reset functionality
3. ✅ Add email verification flow
4. ✅ Create user profile management pages
5. ✅ Implement role-based access control (RBAC)

### Additional Features to Consider
- Two-factor authentication (2FA)
- Social login with Facebook, Apple
- Phone number authentication
- Anonymous authentication for guest users
- Session management and token refresh
- User activity logging

---

## Support and Resources

### Official Documentation
- **Firebase**: https://firebase.google.com/docs
- **Laravel**: https://laravel.com/docs/10.x
- **Vue 3**: https://vuejs.org/guide/introduction.html

### Community Support
- Firebase Discord
- Laravel Forums
- Vue.js Discord

---

## Congratulations! 🎉

You've successfully integrated Firebase Authentication with your Exploria Travel and Tours platform!

**What you've accomplished:**
- ✅ Created Firebase project
- ✅ Configured Firebase Authentication
- ✅ Set up database tables
- ✅ Created Laravel backend API
- ✅ Configured Vue 3 frontend
- ✅ Tested complete authentication flow

**Next:** Start building your Phase 2 features and continue enhancing your travel platform!

---

**Last Updated:** October 2025
**Version:** 1.0
**Author:** Exploria Development Team
