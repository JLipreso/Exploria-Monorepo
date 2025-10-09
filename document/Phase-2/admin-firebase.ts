/**
 * Firebase Configuration - Administrator Portal
 * 
 * This file initializes Firebase for the Exploria Admin Portal
 * Configuration values are loaded from environment variables
 * 
 * Location: frontend/admin-portal/src/firebase.ts
 */

import { initializeApp } from 'firebase/app';
import { getAuth } from 'firebase/auth';

// Firebase configuration from environment variables
const firebaseConfig = {
  apiKey: import.meta.env.VITE_FIREBASE_API_KEY,
  authDomain: import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
  projectId: import.meta.env.VITE_FIREBASE_PROJECT_ID,
  storageBucket: import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
  appId: import.meta.env.VITE_FIREBASE_APP_ID,
};

// Validate Firebase configuration
const validateConfig = () => {
  const requiredKeys = [
    'apiKey',
    'authDomain',
    'projectId',
    'storageBucket',
    'messagingSenderId',
    'appId',
  ];

  const missingKeys = requiredKeys.filter(
    (key) => !firebaseConfig[key as keyof typeof firebaseConfig]
  );

  if (missingKeys.length > 0) {
    console.error(
      '❌ Firebase configuration error: Missing environment variables:',
      missingKeys.map((key) => `VITE_FIREBASE_${key.toUpperCase()}`).join(', ')
    );
    throw new Error('Firebase configuration is incomplete. Check your .env file.');
  }
};

// Validate configuration before initialization
validateConfig();

// Initialize Firebase
const app = initializeApp(firebaseConfig);

// Initialize Firebase Authentication and get a reference to the service
export const auth = getAuth(app);

// Log successful initialization (only in development)
if (import.meta.env.DEV) {
  console.log('✅ Firebase initialized successfully for Admin Portal');
}

// Export the app instance if needed elsewhere
export default app;
