// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAuth, GoogleAuthProvider, signInWithPopup } from "firebase/auth";

// Your web app's Firebase configuration
export const firebaseConfig = {
  apiKey: "AIzaSyBJ5JcyXqo5w_BFC2piheiDHAleBtCUkik",
  authDomain: "workspace-bb7f2.firebaseapp.com",
  projectId: "workspace-bb7f2",
  storageBucket: "workspace-bb7f2.firebasestorage.app",
  messagingSenderId: "1087406887787",
  appId: "1:1087406887787:web:db4736b1ce69069f8af22e"
};

// Initialize Firebase
export const app = initializeApp(firebaseConfig);
export const auth = getAuth(app);
export const googleProvider = new GoogleAuthProvider();
export { signInWithPopup };
