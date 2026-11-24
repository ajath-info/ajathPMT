<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationMember;

class AuthController extends Controller
{
    /**
     * Additional verified Google addresses that belong to the workspace owner (user #1).
     * These only apply AFTER Google has verified the address (never for typed emails).
     */
    private const OWNER_GOOGLE_ALIASES = ['info@ajath.us', 'manjot2306@gmail.com', 'manjot@ajath.in', 'info@ajath.com'];

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Only the account's real password is accepted (the old shared fallback
        // passwords that unlocked ANY account have been removed).
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if (empty($user->email_verified_at)) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
            $this->storeActiveMembership($user);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Demo shortcut that logs in WITHOUT a password. It is only available on a local
     * development machine — never on the live site.
     */
    public function quickLogin(Request $request, $role)
    {
        if (!app()->environment('local') || !in_array($request->ip(), ['127.0.0.1', '::1'], true)) {
            abort(404);
        }

        $roleMap = [
            'owner' => ['manjot@ajath.com', 'claire.watson@ajath.com'],
            'manjot' => ['manjot@ajath.com'],
            'member' => ['shachish@ajath.com', 'marcus.dev@worksphere.io'],
            'client' => ['dharmeshtripathi8@gmail.com', 'edward.client@partner.com'],
        ];

        $candidates = $roleMap[strtolower($role)] ?? ['manjot@ajath.com'];
        $user = User::whereIn('email', $candidates)->orderBy('id', 'asc')->first();

        if ($user) {
            Auth::login($user, true);
            $request->session()->regenerate();
            $this->storeActiveMembership($user);

            return redirect(self::safeRedirectPath($request->query('redirect'), route('dashboard')));
        }

        return redirect()->route('login')->with('error', 'Demo account not found.');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        return back()->with('status', 'We have emailed your password reset instructions to ' . $request->email . '.');
    }

    public function showPricing()
    {
        return view('auth.pricing');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($validated['name']),
            'timezone' => 'Asia/Kolkata',
        ]);

        $slug = Str::slug($validated['company_name']) . '-' . Str::random(4);
        $org = Organization::create([
            'name' => $validated['company_name'],
            'slug' => $slug,
            'owner_id' => $user->id,
            'description' => 'Workspace created by ' . $user->name,
        ]);

        OrganizationMember::create([
            'organization_id' => $org->id,
            'user_id' => $user->id,
            'role' => 'OWNER',
            'department' => 'Executive',
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();
        session([
            'active_org_id' => $org->id,
            'user_role' => 'OWNER',
        ]);

        return redirect()->route('dashboard')->with('success', 'Welcome to ' . $org->name . '! Your workspace is ready.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been logged out.');
    }

    /**
     * Google sign-in via Firebase. The browser sends a Firebase ID token obtained from a
     * real Google popup; the token is verified with Google on the server and only the
     * VERIFIED email from Google is trusted (client-supplied email/name are ignored).
     */
    public function firebaseGoogleLogin(Request $request)
    {
        $mode = (string) $request->input('mode', 'login');
        $idToken = (string) $request->input('id_token', '');

        $identity = $this->verifyFirebaseIdToken($idToken);
        if (!$identity) {
            return response()->json([
                'success' => false,
                'message' => 'Google sign-in could not be verified. Please try again.',
            ], 401);
        }

        $result = $this->completeGoogleSignIn(
            $request,
            $identity['email'],
            $identity['name'],
            $identity['avatar'],
            $mode,
            $request->input('invited_user_id'),
            (string) $request->input('invite_token', '')
        );

        if (!$result['ok']) {
            return response()->json(['success' => false, 'message' => $result['error']], 403);
        }

        return response()->json([
            'success' => true,
            'mode' => $mode,
            'email' => $identity['email'],
            'redirect' => $result['redirect'],
        ]);
    }

    public function redirectToGoogle(Request $request)
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');
        $mode = (string) $request->query('mode', 'login');

        if (empty($clientId)) {
            return redirect()->route($mode === 'switch' && Auth::check() ? 'my.login_option' : 'login')
                ->with('error', 'Google sign-in is not configured on this server. Please log in with your email and password.');
        }

        $state = base64_encode(json_encode([
            'mode' => $mode,
            'invited_user_id' => $request->query('invited_user_id'),
            'invite_token' => (string) $request->query('token', ''),
            'csrf' => csrf_token(),
        ]));

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'access_type' => 'online',
            'prompt' => 'select_account',
            'state' => $state,
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function handleGoogleCallback(Request $request)
    {
        $stateData = json_decode(base64_decode((string) $request->query('state', '')), true) ?: [];
        $mode = $stateData['mode'] ?? 'login';
        $failRoute = ($mode === 'switch' && Auth::check()) ? 'my.login_option' : 'login';

        // The state must round-trip with this browser session's CSRF token (prevents login CSRF)
        if (empty($stateData['csrf']) || !hash_equals((string) csrf_token(), (string) $stateData['csrf'])) {
            return redirect()->route($failRoute)->with('error', 'Google sign-in expired. Please try again.');
        }

        if ($request->has('error') || !$request->has('code')) {
            return redirect()->route($failRoute);
        }

        try {
            $tokenResponse = Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code',
                'code' => $request->query('code'),
            ]);

            if (!$tokenResponse->successful()) {
                return redirect()->route($failRoute)->with('error', 'Google sign-in failed. Please try again.');
            }

            $userResponse = Http::withToken($tokenResponse->json('access_token'))
                ->timeout(15)
                ->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if (!$userResponse->successful()) {
                return redirect()->route($failRoute)->with('error', 'Google sign-in failed. Please try again.');
            }

            $googleUser = $userResponse->json();
            $email = strtolower(trim((string) ($googleUser['email'] ?? '')));
            if ($email === '' || empty($googleUser['email_verified'])) {
                return redirect()->route($failRoute)->with('error', 'Your Google email address is not verified.');
            }

            $result = $this->completeGoogleSignIn(
                $request,
                $email,
                trim((string) ($googleUser['name'] ?? '')),
                trim((string) ($googleUser['picture'] ?? '')),
                $mode,
                $stateData['invited_user_id'] ?? null,
                (string) ($stateData['invite_token'] ?? '')
            );

            if (!$result['ok']) {
                return redirect()->route($failRoute)->with('error', $result['error']);
            }

            return redirect($result['redirect'])->with('success', $mode === 'switch' ? "Switched login to Google ({$email})." : null);
        } catch (\Throwable $e) {
            Log::warning('Google OAuth callback failed: ' . $e->getMessage());
            return redirect()->route($failRoute)->with('error', 'Google sign-in failed. Please try again.');
        }
    }

    /**
     * Shared logic for a Google identity whose email has ALREADY been verified by Google.
     *
     * @return array{ok: bool, error?: string, redirect?: string}
     */
    private function completeGoogleSignIn(Request $request, string $email, string $name, string $avatarUrl, string $mode, $invitedUserId, string $inviteToken): array
    {
        $email = strtolower(trim($email));

        // Mode 1: signed-in user linking Google as their login method in /my/login_option
        if ($mode === 'switch') {
            if (!Auth::check()) {
                return ['ok' => false, 'error' => 'Please log in first.'];
            }
            $currentUser = Auth::user();
            if ($avatarUrl !== '' && empty($currentUser->avatar_url)) {
                $currentUser->avatar_url = $avatarUrl;
                $currentUser->save();
            }
            session([
                'login_method' => 'google',
                'google_linked_email' => $email,
            ]);
            return ['ok' => true, 'redirect' => route('my.login_option')];
        }

        $user = null;

        // Mode 2: invited person accepting an invitation via Google on /join/{user}.
        // Requires the invitation link's token, or a Google address that matches the invite.
        if ($invitedUserId) {
            $invited = User::find($invitedUserId);
            if ($invited && (
                PeopleController::inviteTokenIsValid($invited, $inviteToken)
                || strcasecmp((string) $invited->email, $email) === 0
            )) {
                $user = $invited;
                if ($name !== '') {
                    $user->name = $name;
                }
                if ($avatarUrl !== '') {
                    $user->avatar_url = $avatarUrl;
                }
                $user->save();
            } else {
                return ['ok' => false, 'error' => 'This invitation link is invalid or has already been used. Please sign in with the Google account the invitation was sent to.'];
            }
        } else {
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
            if (!$user && in_array($email, self::OWNER_GOOGLE_ALIASES, true)) {
                $user = User::find(1);
            }
        }

        // Unknown Google accounts are no longer auto-created and added to the company.
        if (!$user) {
            return ['ok' => false, 'error' => "There is no workspace account for {$email}. Ask an administrator to invite you."];
        }

        if ($avatarUrl !== '' && (empty($user->avatar_url) || str_contains((string) $user->avatar_url, 'dicebear.com'))) {
            $user->avatar_url = $avatarUrl;
            $user->save();
        }
        if (empty($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (!$membership && $invitedUserId && ($org = Organization::first())) {
            $membership = OrganizationMember::create([
                'organization_id' => $org->id,
                'user_id' => $user->id,
                'role' => 'MEMBER',
                'department' => 'Team',
            ]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();

        session([
            'active_org_id' => $membership ? $membership->organization_id : (Organization::value('id') ?? 1),
            'user_role' => $membership ? $membership->role : 'MEMBER',
            'login_method' => 'google',
            'google_linked_email' => $email,
        ]);

        return ['ok' => true, 'redirect' => route('dashboard')];
    }

    /**
     * Verify a Firebase ID token with Google's Identity Toolkit and return the verified
     * identity, or null when the token is missing, expired, forged or the email is unverified.
     */
    private function verifyFirebaseIdToken(string $idToken): ?array
    {
        if ($idToken === '' || substr_count($idToken, '.') !== 2) {
            return null;
        }

        $apiKey = config('services.firebase.api_key');
        if (empty($apiKey)) {
            return null;
        }

        try {
            $resp = Http::timeout(10)->post(
                'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . urlencode($apiKey),
                ['idToken' => $idToken]
            );
            if (!$resp->successful()) {
                return null;
            }
            $u = $resp->json('users.0');
            if (!is_array($u) || empty($u['email']) || empty($u['emailVerified'])) {
                return null;
            }

            // Make sure the token was issued for OUR Firebase project
            $payload = json_decode(base64_decode(strtr(explode('.', $idToken)[1], '-_', '+/')), true) ?: [];
            $projectId = config('services.firebase.project_id');
            if ($projectId && ($payload['aud'] ?? null) !== $projectId) {
                return null;
            }

            return [
                'email' => strtolower(trim((string) $u['email'])),
                'name' => trim((string) ($u['displayName'] ?? '')),
                'avatar' => trim((string) ($u['photoUrl'] ?? '')),
            ];
        } catch (\Throwable $e) {
            Log::warning('Firebase ID token verification failed: ' . $e->getMessage());
            return null;
        }
    }

    private function storeActiveMembership(User $user): void
    {
        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if ($membership) {
            session([
                'active_org_id' => $membership->organization_id,
                'user_role' => $membership->role,
            ]);
        }
    }

    /**
     * Only allow same-site relative redirect targets (blocks open redirects such as
     * ?redirect=https://evil.example or //evil.example).
     */
    public static function safeRedirectPath($target, string $fallback = '/'): string
    {
        $target = (string) $target;
        if ($target === '' || $target[0] !== '/' || str_starts_with($target, '//') || str_contains($target, '\\')) {
            return $fallback;
        }
        return $target;
    }
}
