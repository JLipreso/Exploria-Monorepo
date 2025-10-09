# Firebase Configuration Guide
## Setting Up Firebase for Exploria Portals

---

## 📋 Overview

This guide will help you configure Firebase Authentication for all three Exploria portals (Admin, Staff, and Operator).

**Time Required:** 15-20 minutes  
**Difficulty:** Beginner-Intermediate

---

## 🔥 Step 1: Get Firebase Configuration

### 1.1 Access Firebase Console
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Sign in with your Google account
3. Select your Exploria project (or create a new one)

### 1.2 Get Configuration Keys
1. Click on **Project Settings** (gear icon in left sidebar)
2. Scroll down to **Your apps** section
3. Click on the **Web app** (</> icon)
4. If you don't have a web app, click **Add app** and create one
5. You'll see your Firebase configuration:

```javascript
const firebaseConfig = {
  apiKey: "AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
  authDomain: "your-project.firebaseapp.com",
  projectId: "your-project-id",
  storageBucket: "your-project.appspot.com",
  messagingSenderId: "123456789012",
  appId: "1:123456789012:web:abc123def456"
};
```

### 1.3 Copy Your Values
Write down or copy these six values - you'll need them for each portal.

---

## 🎯 Step 2: Configure Each Portal

You need to configure **three portals**: Admin, Staff, and Operator. They all use the **same Firebase project** but are separate applications.

### 2.1 Administrator Portal

```bash
# Navigate to admin portal
cd frontend/admin-portal

# Copy the example file
cp .env.example .env

# Edit the .env file
nano .env
```

**Update these values in `.env`:**
```env
VITE_API_URL=http://localhost:8000
VITE_FIREBASE_API_KEY=AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
VITE_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your-project-id
VITE_FIREBASE_STORAGE_BUCKET=your-project.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=123456789012
VITE_FIREBASE_APP_ID=1:123456789012:web:abc123def456
VITE_APP_VERSION=1.0.0
VITE_APP_NAME=Exploria Admin Portal
VITE_PORTAL_TYPE=admin
```

### 2.2 Staff Portal

```bash
# Navigate to staff portal
cd ../staff-portal

# Copy the example file
cp .env.example .env

# Edit the .env file
nano .env
```

**Update with same Firebase values** (change APP_NAME and PORTAL_TYPE):
```env
VITE_API_URL=http://localhost:8000
VITE_FIREBASE_API_KEY=AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
VITE_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your-project-id
VITE_FIREBASE_STORAGE_BUCKET=your-project.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=123456789012
VITE_FIREBASE_APP_ID=1:123456789012:web:abc123def456
VITE_APP_VERSION=1.0.0
VITE_APP_NAME=Exploria Staff Portal
VITE_PORTAL_TYPE=staff
```

### 2.3 Operator Portal

```bash
# Navigate to operator portal
cd ../operator-portal

# Copy the example file
cp .env.example .env

# Edit the .env file
nano .env
```

**Update with same Firebase values** (change APP_NAME and PORTAL_TYPE):
```env
VITE_API_URL=http://localhost:8000
VITE_FIREBASE_API_KEY=AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX
VITE_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your-project-id
VITE_FIREBASE_STORAGE_BUCKET=your-project.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=123456789012
VITE_FIREBASE_APP_ID=1:123456789012:web:abc123def456
VITE_APP_VERSION=1.0.0
VITE_APP_NAME=Exploria Operator Portal
VITE_PORTAL_TYPE=operator
```

---

## 🔐 Step 3: Enable Authentication Methods

### 3.1 Enable Email/Password Authentication
1. In Firebase Console, click **Authentication** in left sidebar
2. Click **Get Started** (if first time)
3. Click **Sign-in method** tab
4. Click **Email/Password**
5. Toggle **Enable**
6. Click **Save**

### 3.2 Enable Google Sign-In
1. In **Sign-in method** tab
2. Click **Google**
3. Toggle **Enable**
4. Select your support email
5. Click **Save**

### 3.3 (Optional) Enable Other Providers
You can also enable:
- Facebook
- Twitter
- GitHub
- Phone authentication
- Anonymous authentication

---

## 🌐 Step 4: Authorize Domains

Firebase needs to know which domains are allowed to use your authentication.

### 4.1 Add Localhost (Development)
1. In Firebase Console > Authentication
2. Click **Settings** tab
3. Scroll to **Authorized domains**
4. `localhost` should already be there
5. If not, click **Add domain** and add `localhost`

### 4.2 Add Production Domain (Later)
When you deploy to production:
1. Click **Add domain**
2. Enter your production domain (e.g., `admin.exploria.com`)
3. Verify domain ownership if required
4. Click **Add**

---

## ✅ Step 5: Verify Configuration

### 5.1 Check Firebase Files Exist
```bash
# Check each portal has firebase.ts
ls -la frontend/admin-portal/src/firebase.ts
ls -la frontend/staff-portal/src/firebase.ts
ls -la frontend/operator-portal/src/firebase.ts
```

**Expected:** All three files should exist

### 5.2 Check .env Files
```bash
# Check each portal has .env (not .env.example)
ls -la frontend/admin-portal/.env
ls -la frontend/staff-portal/.env
ls -la frontend/operator-portal/.env
```

**Expected:** All three `.env` files should exist with your actual values

### 5.3 Test Firebase Initialization
```bash
# Start one portal
cd frontend/admin-portal
npm run dev
```

Open browser console (F12) and you should see:
```
✅ Firebase initialized successfully for Admin Portal
```

**If you see errors:**
- Check `.env` file has correct values
- Check no typos in environment variable names
- Check Firebase project is active in Firebase Console

---

## 🧪 Step 6: Test Authentication

### 6.1 Create Test User in Firebase
1. Firebase Console > Authentication
2. Click **Users** tab
3. Click **Add user**
4. Enter email: `test@example.com`
5. Enter password: `Test123456`
6. Click **Add user**

### 6.2 Update Database
```sql
-- Make the test user an admin
UPDATE users 
SET confirmed = 1, is_admin = 1
WHERE email = 'test@example.com';
```

### 6.3 Test Login
1. Start admin portal: `npm run dev`
2. Visit: `http://localhost:5173/signin`
3. Login with `test@example.com` / `Test123456`
4. Should redirect to dashboard

---

## 🔧 Common Configuration Issues

### Issue: "Firebase: Error (auth/invalid-api-key)"
**Solution:** Check your `VITE_FIREBASE_API_KEY` in `.env`
- Make sure it's correct from Firebase Console
- No extra spaces
- Restart dev server after changing `.env`

### Issue: "Firebase app named '[DEFAULT]' already exists"
**Solution:** Multiple Firebase initializations
- Check you're not importing firebase.ts multiple times
- Clear browser cache
- Restart dev server

### Issue: "Firebase: Error (auth/unauthorized-domain)"
**Solution:** Domain not authorized
- Add your domain in Firebase Console > Authentication > Settings > Authorized domains
- For local development, make sure `localhost` is listed

### Issue: Environment variables not loading
**Solution:** 
```bash
# Make sure file is named .env (not .env.example)
mv .env.example .env

# Restart dev server
npm run dev
```

---

## 📝 Quick Reference

### Firebase Configuration Checklist
- [ ] Firebase project created
- [ ] Web app added to project
- [ ] Configuration keys copied
- [ ] `.env` files created for all 3 portals
- [ ] All environment variables filled in
- [ ] Email/Password authentication enabled
- [ ] Google Sign-In enabled
- [ ] Localhost domain authorized
- [ ] Firebase initialized successfully (check console)
- [ ] Test user created
- [ ] Test login successful

### Environment Variables Template
```env
VITE_API_URL=http://localhost:8000
VITE_FIREBASE_API_KEY=your-actual-api-key
VITE_FIREBASE_AUTH_DOMAIN=your-project.firebaseapp.com
VITE_FIREBASE_PROJECT_ID=your-project-id
VITE_FIREBASE_STORAGE_BUCKET=your-project.appspot.com
VITE_FIREBASE_MESSAGING_SENDER_ID=your-sender-id
VITE_FIREBASE_APP_ID=your-app-id
VITE_APP_VERSION=1.0.0
VITE_APP_NAME=Exploria [Portal] Portal
VITE_PORTAL_TYPE=[admin|staff|operator]
```

---

## 🆘 Need More Help?

### Firebase Documentation
- [Get Started with Firebase](https://firebase.google.com/docs/web/setup)
- [Firebase Authentication](https://firebase.google.com/docs/auth/web/start)
- [Manage Users](https://firebase.google.com/docs/auth/web/manage-users)

### Exploria Documentation
- `START_HERE.md` - Main installation guide
- `VISUAL_INSTALLATION_GUIDE.md` - Step-by-step with visuals
- `README_PHASE2_IMPLEMENTATION.md` - Complete documentation

---

## ✅ Configuration Complete!

Once you've completed all steps and verified everything works:
1. ✅ All three portals have `.env` files configured
2. ✅ Firebase authentication is enabled
3. ✅ Test login works
4. ✅ No console errors

**You're ready to proceed with the rest of Phase 2 implementation!** 🎉

---

**Last Updated:** October 9, 2025  
**Version:** 2.0
