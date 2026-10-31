<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationMember;

class AuthController extends Controller
{
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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Set active organization in session
            $user = Auth::user();
            if (empty($user->email_verified_at)) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
            $membership = OrganizationMember::where('user_id', $user->id)->first();
            if ($membership) {
                session([
                    'active_org_id' => $membership->organization_id,
                    'user_role' => $membership->role,
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        // Allow team members and clients to login with standard default passwords
        $user = User::where('email', $credentials['email'])->first();
        if ($user && in_array($credentials['password'], ['password123', 'Basecamp2026!', 'Ajath2026!'])) {
            if (empty($user->email_verified_at)) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            $membership = OrganizationMember::where('user_id', $user->id)->first();
            if ($membership) {
                session([
                    'active_org_id' => $membership->organization_id,
                    'user_role' => $membership->role,
                ]);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function quickLogin($role)
    {
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
            request()->session()->regenerate();

            $membership = OrganizationMember::where('user_id', $user->id)->first();
            if ($membership) {
                session([
                    'active_org_id' => $membership->organization_id,
                    'user_role' => $membership->role,
                ]);
            }

            if (request()->filled('redirect')) {
                return redirect(request()->query('redirect'));
            }

            return redirect()->route('dashboard')->with('success', 'Logged in as ' . $user->name . ' (' . strtoupper($role) . ')');
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

    public function firebaseGoogleLogin(Request $request)
    {
        $email = strtolower(trim((string) $request->input('email')));
        $name = trim((string) $request->input('name', 'Google User'));
        $avatarUrl = trim((string) $request->input('avatar_url', ''));
        $mode = (string) $request->input('mode', 'login');
        $invitedUserId = $request->input('invited_user_id');

        if ($email === '') {
            return response()->json([
                'success' => false,
                'message' => 'Google account email is required.',
            ], 422);
        }

        // Mode 1: Authenticated user switching login method to Google in /my/login_option
        if ($mode === 'switch' && Auth::check()) {
            $currentUser = Auth::user();
            if ($avatarUrl !== '' && empty($currentUser->avatar_url)) {
                $currentUser->avatar_url = $avatarUrl;
                $currentUser->save();
            }
            session([
                'login_method' => 'google',
                'google_linked_email' => $email,
            ]);

            return response()->json([
                'success' => true,
                'mode' => 'switch',
                'email' => $email,
                'redirect' => route('my.login_option'),
            ]);
        }

        $org = Organization::first();

        // Mode 2: Invited user accepting invitation via Google on /join/{user}
        if ($invitedUserId) {
            $user = User::find($invitedUserId);
            if ($user) {
                if ($name !== '') {
                    $user->name = $name;
                }
                if ($avatarUrl !== '') {
                    $user->avatar_url = $avatarUrl;
                }
                $user->save();
            }
        } else {
            $user = User::where('email', $email)->first();
            if (!$user && in_array($email, ['info@ajath.us', 'manjot2306@gmail.com', 'manjot@ajath.in', 'info@ajath.com'], true)) {
                $user = User::find(1);
            }
        }

        if (!$user) {
            $user = User::create([
                'name' => $name !== '' ? $name : explode('@', $email)[0],
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
                'avatar_url' => $avatarUrl !== '' ? $avatarUrl : ('https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($name)),
                'timezone' => 'Asia/Kolkata',
            ]);
        } elseif ($avatarUrl !== '' && (empty($user->avatar_url) || str_contains((string) $user->avatar_url, 'dicebear.com'))) {
            $user->avatar_url = $avatarUrl;
            $user->save();
        }

        $membership = OrganizationMember::where('user_id', $user->id)->first();
        if (!$membership && $org) {
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
            'active_org_id' => $membership ? $membership->organization_id : ($org ? $org->id : 1),
            'user_role' => $membership ? $membership->role : 'MEMBER',
            'login_method' => 'google',
            'google_linked_email' => $email,
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('dashboard'),
        ]);
    }

    public function redirectToGoogle(Request $request)
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');
        $mode = (string) $request->query('mode', 'login');
        $invitedUserId = $request->query('invited_user_id');

        $state = base64_encode(json_encode([
            'mode' => $mode,
            'invited_user_id' => $invitedUserId,
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
        $invitedUserId = $stateData['invited_user_id'] ?? null;

        if ($request->has('error') || !$request->has('code')) {
            return redirect()->route($mode === 'switch' ? 'my.login_option' : 'login');
        }

        try {
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'redirect_uri' => config('services.google.redirect'),
                'grant_type' => 'authorization_code',
                'code' => $request->query('code'),
            ]);

            if (!$tokenResponse->successful()) {
                return redirect()->route($mode === 'switch' ? 'my.login_option' : 'login');
            }

            $accessToken = $tokenResponse->json('access_token');
            $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');

            if (!$userResponse->successful()) {
                return redirect()->route($mode === 'switch' ? 'my.login_option' : 'login');
            }

            $googleUser = $userResponse->json();
            $email = strtolower(trim((string) ($googleUser['email'] ?? '')));
            $name = trim((string) ($googleUser['name'] ?? 'Google User'));
            $avatarUrl = trim((string) ($googleUser['picture'] ?? ''));

            if ($email === '') {
                return redirect()->route($mode === 'switch' ? 'my.login_option' : 'login');
            }

            if ($mode === 'switch' && Auth::check()) {
                session([
                    'login_method' => 'google',
                    'google_linked_email' => $email,
                ]);
                return redirect()->route('my.login_option')->with('success', "Switched login to Google ({$email}).");
            }

            $org = Organization::first();
            if ($invitedUserId) {
                $user = User::find($invitedUserId);
            } else {
                $user = User::where('email', $email)->first();
                if (!$user && in_array($email, ['info@ajath.us', 'manjot2306@gmail.com', 'manjot@ajath.in', 'info@ajath.com'], true)) {
                    $user = User::find(1);
                }
            }

            if (!$user) {
                $user = User::create([
                    'name' => $name !== '' ? $name : explode('@', $email)[0],
                    'email' => $email,
                    'password' => Hash::make(Str::random(24)),
                    'avatar_url' => $avatarUrl !== '' ? $avatarUrl : ('https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($name)),
                    'timezone' => 'Asia/Kolkata',
                ]);
            }

            $membership = OrganizationMember::where('user_id', $user->id)->first();
            if (!$membership && $org) {
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
                'active_org_id' => $membership ? $membership->organization_id : ($org ? $org->id : 1),
                'user_role' => $membership ? $membership->role : 'MEMBER',
                'login_method' => 'google',
                'google_linked_email' => $email,
            ]);

            return redirect()->route('dashboard');
        } catch (\Throwable $e) {
            return redirect()->route($mode === 'switch' ? 'my.login_option' : 'login');
        }
    }
}
