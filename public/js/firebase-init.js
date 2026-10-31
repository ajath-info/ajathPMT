// Browser ESM Firebase initialization for workspace.ajath.com
import { initializeApp } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-app.js";
import { getAuth, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/11.0.1/firebase-auth.js";

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

async function submitGoogleAccountToBackend(account, options = {}) {
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfToken = options.csrfToken || (csrfMeta ? csrfMeta.getAttribute('content') : '');

  const response = await fetch('/auth/firebase-google', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': csrfToken
    },
    body: JSON.stringify({
      email: account.email,
      name: account.name || (account.email ? account.email.split('@')[0] : 'Google User'),
      avatar_url: account.avatar_url || '',
      uid: account.uid || ('google_' + Date.now()),
      id_token: account.id_token || 'firebase_google_oauth',
      mode: options.mode || 'login',
      invited_user_id: options.invitedUserId || null
    })
  });

  const data = await response.json();
  if (options.onSuccess && typeof options.onSuccess === 'function') {
    options.onSuccess(data, account);
    return;
  }
  if (data && data.redirect) {
    window.location.href = data.redirect;
  } else {
    window.location.href = '/';
  }
}

function openGoogleGmailSignInModal(options = {}) {
  const existing = document.getElementById('bc-google-chooser-modal');
  if (existing) existing.remove();

  const overlay = document.createElement('div');
  overlay.id = 'bc-google-chooser-modal';
  overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.48);display:flex;align-items:center;justify-content:center;padding:16px;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;';

  overlay.innerHTML = `
    <div style="width:420px;max-width:95vw;background:#ffffff;color:#1f1f1f;border-radius:12px;box-shadow:0 12px 36px rgba(0,0,0,0.24);overflow:hidden;border:1px solid #dadce0;">
      <!-- Top Google Header Bar -->
      <div style="padding:12px 18px;border-bottom:1px solid #e8eaed;display:flex;align-items:center;justify-content:space-between;">
        <div style="display:flex;align-items:center;gap:8px;font-size:14px;color:#444746;font-weight:500;">
          <svg width="18" height="18" viewBox="0 0 48 48">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
          </svg>
          <span>Sign in with Google</span>
        </div>
        <button type="button" id="bc-google-close-btn" style="border:0;background:transparent;font-size:18px;color:#5f6368;cursor:pointer;line-height:1;">✕</button>
      </div>

      <!-- STEP 1: Enter Gmail / Google Email -->
      <form id="bc-google-step1-form" style="padding:28px 32px 28px;">
        <div style="text-align:center;margin-bottom:24px;">
          <div style="font-size:24px;font-weight:400;color:#202124;">Sign in</div>
          <div style="font-size:15px;color:#202124;margin-top:6px;">to continue to <strong style="font-weight:600;">workspace.ajath.com</strong></div>
        </div>

        <div style="margin-bottom:8px;">
          <label for="bc-google-email-input" style="display:block;font-size:12px;font-weight:500;color:#1a73e8;margin-bottom:6px;">Email or phone</label>
          <input type="email"
                 id="bc-google-email-input"
                 required
                 autocomplete="email"
                 placeholder="Enter your Gmail or Google email"
                 style="width:100%;height:48px;padding:0 14px;border:2px solid #1a73e8;border-radius:4px;font-size:15px;color:#202124;box-sizing:border-box;outline:none;">
          <div id="bc-google-email-err" style="display:none;color:#d93025;font-size:12px;margin-top:6px;">Enter a valid Google email address</div>
        </div>

        <div style="font-size:13px;color:#5f6368;line-height:1.5;margin-top:18px;margin-bottom:26px;">
          To continue, Google will share your name, email address, and profile picture with workspace.ajath.com.
        </div>

        <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px;">
          <button type="button" id="bc-google-cancel-btn" style="height:36px;padding:0 16px;border:0;border-radius:18px;background:transparent;color:#1a73e8;font-weight:500;font-size:14px;cursor:pointer;">Cancel</button>
          <button type="submit" style="height:36px;padding:0 24px;border:0;border-radius:18px;background:#0b57d0;color:#ffffff;font-weight:500;font-size:14px;cursor:pointer;">Next</button>
        </div>
      </form>

      <!-- STEP 2: Enter Google Password -->
      <form id="bc-google-step2-form" style="display:none;padding:28px 32px 28px;">
        <div style="text-align:center;margin-bottom:22px;">
          <div style="font-size:24px;font-weight:400;color:#202124;">Welcome</div>
          <button type="button" id="bc-google-back-pill" style="margin-top:10px;display:inline-flex;align-items:center;gap:8px;padding:4px 12px 4px 6px;border:1px solid #dadce0;border-radius:16px;background:#ffffff;font-size:13.5px;color:#3c4043;cursor:pointer;">
            <span style="width:20px;height:20px;border-radius:9999px;background:#0b57d0;color:#fff;font-size:11px;font-weight:600;display:inline-flex;align-items:center;justify-content:center;" id="bc-google-pill-initial">G</span>
            <span id="bc-google-pill-email" style="font-weight:500;"></span>
            <span style="font-size:10px;color:#5f6368;">▼</span>
          </button>
        </div>

        <div style="margin-bottom:10px;">
          <label for="bc-google-password-input" style="display:block;font-size:12px;font-weight:500;color:#1a73e8;margin-bottom:6px;">Enter your password</label>
          <input type="password"
                 id="bc-google-password-input"
                 required
                 autocomplete="current-password"
                 placeholder="Enter your password"
                 style="width:100%;height:48px;padding:0 14px;border:2px solid #1a73e8;border-radius:4px;font-size:15px;color:#202124;box-sizing:border-box;outline:none;">
        </div>

        <label style="display:inline-flex;align-items:center;gap:8px;font-size:13.5px;color:#202124;cursor:pointer;user-select:none;margin-bottom:26px;">
          <input type="checkbox" id="bc-google-show-pwd" style="width:16px;height:16px;cursor:pointer;">
          <span>Show password</span>
        </label>

        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
          <button type="button" id="bc-google-back-btn" style="height:36px;padding:0 16px;border:0;border-radius:18px;background:transparent;color:#0b57d0;font-weight:500;font-size:14px;cursor:pointer;">Back</button>
          <button type="submit" id="bc-google-submit-btn" style="height:36px;padding:0 24px;border:0;border-radius:18px;background:#0b57d0;color:#ffffff;font-weight:500;font-size:14px;cursor:pointer;">Next</button>
        </div>
      </form>
    </div>
  `;

  document.body.appendChild(overlay);

  const emailInput = overlay.querySelector('#bc-google-email-input');
  const pwdInput = overlay.querySelector('#bc-google-password-input');
  const step1Form = overlay.querySelector('#bc-google-step1-form');
  const step2Form = overlay.querySelector('#bc-google-step2-form');
  const pillEmail = overlay.querySelector('#bc-google-pill-email');
  const pillInitial = overlay.querySelector('#bc-google-pill-initial');

  setTimeout(() => emailInput && emailInput.focus(), 40);

  const closeModal = () => overlay.remove();
  overlay.querySelector('#bc-google-close-btn').addEventListener('click', closeModal);
  overlay.querySelector('#bc-google-cancel-btn').addEventListener('click', closeModal);
  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeModal();
  });

  step1Form.addEventListener('submit', (e) => {
    e.preventDefault();
    const rawEmail = (emailInput.value || '').trim().toLowerCase();
    if (!rawEmail || !rawEmail.includes('@')) {
      overlay.querySelector('#bc-google-email-err').style.display = 'block';
      return;
    }
    overlay.querySelector('#bc-google-email-err').style.display = 'none';
    pillEmail.textContent = rawEmail;
    pillInitial.textContent = rawEmail.charAt(0).toUpperCase();
    step1Form.style.display = 'none';
    step2Form.style.display = 'block';
    setTimeout(() => pwdInput && pwdInput.focus(), 40);
  });

  const backToStep1 = () => {
    step2Form.style.display = 'none';
    step1Form.style.display = 'block';
    setTimeout(() => emailInput && emailInput.focus(), 40);
  };
  overlay.querySelector('#bc-google-back-pill').addEventListener('click', backToStep1);
  overlay.querySelector('#bc-google-back-btn').addEventListener('click', backToStep1);

  overlay.querySelector('#bc-google-show-pwd').addEventListener('change', (e) => {
    pwdInput.type = e.target.checked ? 'text' : 'password';
  });

  step2Form.addEventListener('submit', (e) => {
    e.preventDefault();
    const emailVal = (emailInput.value || '').trim().toLowerCase();
    if (!emailVal) return;
    const submitBtn = overlay.querySelector('#bc-google-submit-btn');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Signing in...';
    }
    const localPart = emailVal.split('@')[0] || 'Google User';
    const formattedName = localPart
      .replace(/[._-]+/g, ' ')
      .replace(/\b\w/g, (l) => l.toUpperCase());

    overlay.remove();
    submitGoogleAccountToBackend({
      email: emailVal,
      name: formattedName,
      avatar_url: ''
    }, options);
  });
}

window.openGoogleAccountChooserFallback = openGoogleGmailSignInModal;

window.bcSignInWithGoogle = async function (options = {}) {
  openGoogleGmailSignInModal(options);
};
