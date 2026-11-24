// Browser ESM Firebase initialization for workspace.ajath.com
//
// Google sign-in uses Firebase's real Google popup. The resulting Firebase ID token is sent
// to the server, which verifies it with Google before logging anyone in. (The previous
// version showed a look-alike "Google" form that accepted any typed email and password
// without contacting Google, which let anyone sign in as any user.)
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
import { getAuth, GoogleAuthProvider, signInWithPopup, signOut } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-auth.js";

const firebaseConfig = {
  apiKey: "AIzaSyBJ5JcyXqo5w_BFC2piheiDHAleBtCUkik",
  authDomain: "workspace-bb7f2.firebaseapp.com",
  projectId: "workspace-bb7f2",
  storageBucket: "workspace-bb7f2.firebasestorage.app",
  messagingSenderId: "1087406887787",
  appId: "1:1087406887787:web:db4736b1ce69069f8af22e"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const googleProvider = new GoogleAuthProvider();
googleProvider.setCustomParameters({ prompt: 'select_account' });

window.bcFirebaseApp = app;
window.bcFirebaseAuth = auth;

function serverOAuthUrl(options = {}) {
  const params = new URLSearchParams({ mode: options.mode || 'login' });
  if (options.invitedUserId) params.set('invited_user_id', String(options.invitedUserId));
  if (options.inviteToken) params.set('token', String(options.inviteToken));
  return '/auth/google/redirect?' + params.toString();
}

async function submitIdTokenToBackend(idToken, options = {}) {
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = options.csrfToken || (csrfMeta ? csrfMeta.getAttribute('content') : '');

  const response = await fetch('/auth/firebase-google', {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
      id_token: idToken,
      mode: options.mode || 'login',
      invited_user_id: options.invitedUserId || null,
      invite_token: options.inviteToken || null
    })
  });

  let data = null;
  try { data = await response.json(); } catch (e) { data = null; }

  if (!response.ok || !data || data.success === false) {
    const msg = (data && data.message) ? data.message : 'Google sign-in failed. Please try again.';
    alert(msg);
    return;
  }

  if (options.onSuccess && typeof options.onSuccess === 'function') {
    options.onSuccess(data);
    return;
  }
  window.location.href = data.redirect || '/';
}

window.bcSignInWithGoogle = async function (options = {}) {
  try {
    const result = await signInWithPopup(auth, googleProvider);
    const idToken = await result.user.getIdToken(/* forceRefresh */ true);
    // The app uses its own Laravel session; don't keep a separate Firebase session around.
    try { await signOut(auth); } catch (e) {}
    await submitIdTokenToBackend(idToken, options);
  } catch (err) {
    const code = (err && err.code) || '';
    if (code === 'auth/popup-closed-by-user' || code === 'auth/cancelled-popup-request') {
      return; // user closed the Google window
    }
    if (code === 'auth/popup-blocked' || code === 'auth/operation-not-supported-in-this-environment'
        || code === 'auth/unauthorized-domain' || code === 'auth/operation-not-allowed') {
      // Fall back to the server-side Google OAuth flow (verified by Google on the server too)
      window.location.href = serverOAuthUrl(options);
      return;
    }
    console.error('Google sign-in error', err);
    alert('Google sign-in failed. Please try again or log in with your email and password.');
  }
};
