# 📦 Visual Installation Guide
## Exploria Phase 2 - File Placement

---

## 📁 Where to Place Each File

```
your-exploria-project/
│
├── 📂 backend/laravel/
│   ├── 📂 app/Http/Controllers/Portal/
│   │   └── 📄 AuthPortalController.php ← COPY HERE
│   │       (from: backend/laravel/app/Http/Controllers/Portal/)
│   │
│   └── 📂 routes/
│       └── 📄 api.php ← ADD ROUTES FROM: portal_auth_routes.php
│
├── 📂 packages/shared-core/shared-services/
│   └── 📄 AuthPortalServices.ts ← COPY HERE
│       (from: packages/shared-core/shared-services/)
│
└── 📂 frontend/
    │
    ├── 📂 admin-portal/
    │   └── 📂 src/
    │       ├── 📂 views/
    │       │   └── 📄 SignInPage.vue ← COPY HERE
    │       │       (from: frontend/admin-portal/src/views/)
    │       │
    │       ├── 📄 firebase.ts ← CREATE/UPDATE
    │       ├── 📂 router/
    │       │   └── 📄 index.ts ← UPDATE (add SignIn route)
    │       └── 📄 .env ← UPDATE (add Firebase config)
    │
    ├── 📂 staff-portal/
    │   └── 📂 src/
    │       ├── 📂 views/
    │       │   └── 📄 SignInPage.vue ← COPY HERE
    │       │       (from: frontend/staff-portal/src/views/)
    │       │
    │       ├── 📄 firebase.ts ← CREATE/UPDATE
    │       ├── 📂 router/
    │       │   └── 📄 index.ts ← UPDATE (add SignIn route)
    │       └── 📄 .env ← UPDATE (add Firebase config)
    │
    └── 📂 operator-portal/
        └── 📂 src/
            ├── 📂 views/
            │   └── 📄 SignInPage.vue ← COPY HERE
            │       (from: frontend/operator-portal/src/views/)
            │
            ├── 📄 firebase.ts ← CREATE/UPDATE
            ├── 📂 router/
            │   └── 📄 index.ts ← UPDATE (add SignIn route)
            └── 📄 .env ← UPDATE (add Firebase config)
```

---

## 🔧 Step-by-Step Installation

### STEP 1: Backend Controller (5 min)

```bash
# From the package directory:
cp backend/laravel/app/Http/Controllers/Portal/AuthPortalController.php \
   YOUR_PROJECT/backend/laravel/app/Http/Controllers/Portal/

# Verify the file is in the correct location:
ls -la YOUR_PROJECT/backend/laravel/app/Http/Controllers/Portal/
```

**✅ Expected result:** You should see `AuthPortalController.php` in the Portal directory

---

### STEP 2: Backend Routes (3 min)

```bash
# Open your project's api.php file:
nano YOUR_PROJECT/backend/laravel/routes/api.php

# Add this at the top (after other use statements):
use App\Http\Controllers\Portal\AuthPortalController;

# Add these routes (at the end of the file):
Route::prefix('api/portal/auth')->group(function () {
    Route::post('/login', [AuthPortalController::class, 'login']);
    Route::post('/admin/login', [AuthPortalController::class, 'adminLogin']);
    Route::post('/staff/login', [AuthPortalController::class, 'staffLogin']);
    Route::post('/operator/login', [AuthPortalController::class, 'operatorLogin']);
    Route::post('/logout', [AuthPortalController::class, 'logout']);
    Route::post('/verify-session', [AuthPortalController::class, 'verifySession']);
});

# Save and exit (Ctrl+X, Y, Enter)
```

**✅ Expected result:** Routes should be accessible at `/api/portal/auth/*`

---

### STEP 3: Test Backend (2 min)

```bash
# Start Laravel server:
cd YOUR_PROJECT/backend/laravel
php artisan serve

# In another terminal, test the endpoint:
curl -X POST http://localhost:8000/api/portal/auth/admin/login

# You should see a validation error (422) - this is correct!
# It means the endpoint is working but missing required fields
```

**✅ Expected result:** JSON response with validation error

---

### STEP 4: Frontend Service (3 min)

```bash
# Copy the TypeScript service:
cp packages/shared-core/shared-services/AuthPortalServices.ts \
   YOUR_PROJECT/packages/shared-core/shared-services/

# Verify:
ls -la YOUR_PROJECT/packages/shared-core/shared-services/AuthPortalServices.ts
```

**✅ Expected result:** File exists in shared-services directory

---

### STEP 5: Admin Portal (5 min)

```bash
# Copy SignInPage.vue:
cp frontend/admin-portal/src/views/SignInPage.vue \
   YOUR_PROJECT/frontend/admin-portal/src/views/

# Create/Update firebase.ts:
cat > YOUR_PROJECT/frontend/admin-portal/src/firebase.ts << 'EOF'
import { initializeApp } from 'firebase/app';
import { getAuth } from 'firebase/auth';

const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId: import.meta.env.VITE_FIREBASE_APP_ID,
};

const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
EOF

# Update .env (add your Firebase config):
nano YOUR_PROJECT/frontend/admin-portal/.env
```

**Add to .env:**
```env
VITE_API_URL=http://localhost:8000
VITE_FIREBASE_API_KEY=your-api-key
VITE_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your-project-id
VITE_FIREBASE_STORAGE_BUCKET=your-project.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=123456789
VITE_FIREBASE_APP_ID=1:123456789:web:abc123
VITE_APP_VERSION=1.0.0
```

**✅ Expected result:** SignInPage.vue and firebase.ts are in place, .env is updated

---

### STEP 6: Staff Portal (3 min)

```bash
# Repeat the same process for Staff Portal:
cp frontend/staff-portal/src/views/SignInPage.vue \
   YOUR_PROJECT/frontend/staff-portal/src/views/

# Copy firebase.ts from admin portal or create new:
cp YOUR_PROJECT/frontend/admin-portal/src/firebase.ts \
   YOUR_PROJECT/frontend/staff-portal/src/firebase.ts

# Copy .env from admin portal or create new:
cp YOUR_PROJECT/frontend/admin-portal/.env \
   YOUR_PROJECT/frontend/staff-portal/.env
```

**✅ Expected result:** Staff portal has same structure as admin portal

---

### STEP 7: Operator Portal (3 min)

```bash
# Repeat for Operator Portal:
cp frontend/operator-portal/src/views/SignInPage.vue \
   YOUR_PROJECT/frontend/operator-portal/src/views/

cp YOUR_PROJECT/frontend/admin-portal/src/firebase.ts \
   YOUR_PROJECT/frontend/operator-portal/src/firebase.ts

cp YOUR_PROJECT/frontend/admin-portal/.env \
   YOUR_PROJECT/frontend/operator-portal/.env
```

**✅ Expected result:** Operator portal has same structure as other portals

---

### STEP 8: Install Dependencies (2 min)

```bash
# For each portal, install required packages:

# Admin Portal
cd YOUR_PROJECT/frontend/admin-portal
npm install firebase bootstrap-icons

# Staff Portal
cd YOUR_PROJECT/frontend/staff-portal
npm install firebase bootstrap-icons

# Operator Portal
cd YOUR_PROJECT/frontend/operator-portal
npm install firebase bootstrap-icons
```

**✅ Expected result:** All dependencies installed successfully

---

### STEP 9: Update Routers (3 min)

For **each portal** (admin, staff, operator), update the router:

```typescript
// YOUR_PROJECT/frontend/[portal-name]/src/router/index.ts

import SignInPage from '@/views/SignInPage.vue';

const routes = [
  {
    path: '/signin',
    name: 'SignIn',
    component: SignInPage,
    meta: { requiresAuth: false }
  },
  // ... other routes
];
```

**✅ Expected result:** SignIn route accessible at `/signin`

---

### STEP 10: Test Frontend (3 min)

```bash
# Start admin portal:
cd YOUR_PROJECT/frontend/admin-portal
npm run dev

# Open browser: http://localhost:5173/signin
# You should see the sign-in page!
```

**✅ Expected result:** Beautiful sign-in page with Exploria theme

---

## ✅ Installation Checklist

### Backend
- [ ] AuthPortalController.php copied to Portal directory
- [ ] Routes added to api.php
- [ ] Backend tested with curl
- [ ] No errors in Laravel logs

### Frontend Service
- [ ] AuthPortalServices.ts copied to shared-services
- [ ] File is accessible by all portals

### Admin Portal
- [ ] SignInPage.vue copied
- [ ] firebase.ts created/updated
- [ ] .env updated with Firebase config
- [ ] Router updated with SignIn route
- [ ] Dependencies installed
- [ ] Portal tested in browser

### Staff Portal
- [ ] SignInPage.vue copied
- [ ] firebase.ts created/updated
- [ ] .env updated
- [ ] Router updated
- [ ] Dependencies installed
- [ ] Portal tested in browser

### Operator Portal
- [ ] SignInPage.vue copied
- [ ] firebase.ts created/updated
- [ ] .env updated
- [ ] Router updated
- [ ] Dependencies installed
- [ ] Portal tested in browser

---

## 🎯 Verification Tests

### Test 1: Backend Endpoints
```bash
curl -X POST http://localhost:8000/api/portal/auth/admin/login \
  -H "Content-Type: application/json" \
  -d '{}'
```
**Expected:** 422 validation error (correct!)

### Test 2: Frontend Loading
```bash
# Start portal
cd YOUR_PROJECT/frontend/admin-portal
npm run dev
```
**Expected:** No errors, sign-in page loads

### Test 3: Firebase Connection
- Open browser console (F12)
- Visit sign-in page
- Check for Firebase initialization messages
**Expected:** No Firebase errors

### Test 4: Complete Login Flow
1. Create test admin user in database
2. Sign in with email/password
3. Check browser console for success
4. Verify redirect to dashboard
**Expected:** Login successful, redirected to dashboard

---

## 🐛 Troubleshooting

### Issue: "Cannot find module"
```bash
# Make sure path is correct:
ls -la YOUR_PROJECT/packages/shared-core/shared-services/AuthPortalServices.ts

# Check if you need to adjust import paths in SignInPage.vue
```

### Issue: Firebase errors
```bash
# Verify .env has correct values
cat YOUR_PROJECT/frontend/admin-portal/.env | grep FIREBASE

# Check Firebase Console > Project Settings
```

### Issue: CORS errors
```php
// In YOUR_PROJECT/backend/laravel/config/cors.php
'paths' => ['api/*'],
'allowed_origins' => ['http://localhost:3000', 'http://localhost:5173'],
```

---

## 🎉 Success!

If all checks pass, your Phase 2 implementation is complete!

**Total installation time:** ~30 minutes  
**Files installed:** 6 implementation files  
**Portals configured:** 3 (Admin, Staff, Operator)

**Next:** Test with real users and move to Phase 3!

---

**Quick Help:**
- Full documentation: `README_PHASE2_IMPLEMENTATION.md`
- Quick reference: `QUICK_REFERENCE.md`
- Package summary: `PACKAGE_SUMMARY.md`
