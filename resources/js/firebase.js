import { initializeApp } from 'firebase/app';
import { 
  getAuth, 
  signInWithEmailAndPassword, 
  createUserWithEmailAndPassword, 
  signInWithPopup, 
  GoogleAuthProvider, 
  signOut, 
  onAuthStateChanged 
} from 'firebase/auth';
import { 
  getFirestore, 
  collection, 
  doc, 
  getDoc, 
  getDocs, 
  setDoc, 
  addDoc, 
  updateDoc, 
  deleteDoc, 
  query, 
  where, 
  orderBy, 
  serverTimestamp 
} from 'firebase/firestore';
import { getDataConnect, connectDataConnectEmulator } from 'firebase/data-connect';

// Official Firebase Project Credentials for SSMIS Web App (ssmis-ea5df)
const firebaseConfig = {
  apiKey: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_FIREBASE_API_KEY) || "AIzaSyBZAwnzO7XixKyPAfrJJVQcS1hCEHut5Tc",
  authDomain: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_FIREBASE_AUTH_DOMAIN) || "ssmis-ea5df.firebaseapp.com",
  projectId: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_FIREBASE_PROJECT_ID) || "ssmis-ea5df",
  storageBucket: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_FIREBASE_STORAGE_BUCKET) || "ssmis-ea5df.firebasestorage.app",
  messagingSenderId: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_FIREBASE_MESSAGING_SENDER_ID) || "146408905787",
  appId: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_FIREBASE_APP_ID) || "1:146408905787:web:6907a2030cad7690d4501e",
  measurementId: (typeof import.meta !== 'undefined' && import.meta.env?.VITE_FIREBASE_MEASUREMENT_ID) || "G-6B9P5HHRP2"
};

// 1. Initialize Firebase Core
export const app = initializeApp(firebaseConfig);

// 2. Initialize Firebase Authentication
export const auth = getAuth(app);
export const googleProvider = new GoogleAuthProvider();

// 3. Initialize Cloud Firestore Database
export const db = getFirestore(app);

// 4. Initialize Firebase SQL Connect / Data Connect
export const dataConnect = getDataConnect(app, {
  connector: 'ss-mis-connector',
  service: 'ss-mis-dataconnect',
  location: 'us-central1'
});

import { connectFirestoreEmulator } from 'firebase/firestore';
import { connectAuthEmulator } from 'firebase/auth';

// Connect to local Emulators during local development only
const isLocalhost = typeof window !== 'undefined' && (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1');
if (import.meta.env?.DEV || isLocalhost) {
  try { connectDataConnectEmulator(dataConnect, '127.0.0.1', 9399, false); } catch (_) {}
  try { connectFirestoreEmulator(db, '127.0.0.1', 8080); } catch (_) {}
  try { connectAuthEmulator(auth, 'http://127.0.0.1:9099'); } catch (_) {}
  console.log('[Firebase Emulators] Connected locally to Auth (9099), Firestore (8080), DataConnect (9399)');
}

// ---------------------------------------------------------------------------
// AUTHENTICATION HELPERS (Email/Password & Google Sign-In)
// ---------------------------------------------------------------------------

/**
 * Sign in with Email and Password
 */
export async function loginWithEmail(email, password) {
  try {
    const userCredential = await signInWithEmailAndPassword(auth, email, password);
    console.log('✅ Email Sign-In Success:', userCredential.user.email);
    return { success: true, user: userCredential.user };
  } catch (error) {
    console.error('❌ Email Sign-In Error:', error.message);
    return { success: false, error: error.message };
  }
}

/**
 * Sign up with Email and Password
 */
export async function registerWithEmail(email, password, displayName = '') {
  try {
    const userCredential = await createUserWithEmailAndPassword(auth, email, password);
    const user = userCredential.user;
    
    // Save user profile to Firestore
    await setDoc(doc(db, 'users', user.uid), {
      uid: user.uid,
      email: user.email,
      displayName: displayName || user.email.split('@')[0],
      createdAt: serverTimestamp()
    });

    console.log('✅ User Registered Successfully:', user.email);
    return { success: true, user };
  } catch (error) {
    console.error('❌ User Registration Error:', error.message);
    return { success: false, error: error.message };
  }
}

/**
 * Sign in with Google Popup
 */
export async function loginWithGoogle() {
  try {
    const result = await signInWithPopup(auth, googleProvider);
    const user = result.user;

    // Save/update user profile in Firestore
    await setDoc(doc(db, 'users', user.uid), {
      uid: user.uid,
      email: user.email,
      displayName: user.displayName,
      photoURL: user.photoURL,
      lastLogin: serverTimestamp()
    }, { merge: true });

    console.log('✅ Google Sign-In Success:', user.displayName || user.email);
    return { success: true, user };
  } catch (error) {
    console.error('❌ Google Sign-In Error:', error.message);
    return { success: false, error: error.message };
  }
}

/**
 * Sign Out current user
 */
export async function logout() {
  try {
    await signOut(auth);
    console.log('✅ Signed out successfully');
    return { success: true };
  } catch (error) {
    console.error('❌ Logout Error:', error.message);
    return { success: false, error: error.message };
  }
}

/**
 * Listen to Authentication State Changes
 */
export function onAuthChange(callback) {
  return onAuthStateChanged(auth, callback);
}

// ---------------------------------------------------------------------------
// DATA CONNECT VERIFICATION HELPER
// ---------------------------------------------------------------------------
export async function testFirebaseConnection() {
  try {
    const { listStores } = await import('./dataconnect/esm/index.esm.js');
    const res = await listStores(dataConnect);
    const successMsg = `✅ Firebase SQL Connect Connected Successfully! Database returned ${res.data.stores.length} store records.`;
    console.log(successMsg);
    return successMsg;
  } catch (err) {
    const errorMsg = `❌ Firebase Connection Error: ${err.message}`;
    console.error(errorMsg);
    return errorMsg;
  }
}
