
import { initializeApp } from 'firebase/app';
import { getAuth } from 'firebase/auth';
import { project } from '@exploria/shared-core'; 

const firebaseConfig = {
  apiKey: project.firebaseConfig.staff.apiKey,
  authDomain: project.firebaseConfig.staff.authDomain,
  projectId: project.firebaseConfig.staff.projectId,
  storageBucket: project.firebaseConfig.staff.storageBucket,
  messagingSenderId: project.firebaseConfig.staff.messagingSenderId,
  appId: project.firebaseConfig.staff.appId
};

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

if (project.app.dev) {
  console.log('✅ Firebase initialized successfully for Admin Portal');
}

export default app;