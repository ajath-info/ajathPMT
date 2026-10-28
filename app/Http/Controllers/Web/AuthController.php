<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'owner' => 'claire.watson@ajath.com',
            'member' => 'marcus.dev@worksphere.io',
            'client' => 'edward.client@partner.com',
        ];

        $email = $roleMap[strtolower($role)] ?? 'claire.watson@ajath.com';
        $user = User::where('email', $email)->first();

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

            return redirect()->route('dashboard')->with('success', 'Logged in as ' . $user->name . ' (' . strtoupper($role) . ')');
        }

        return redirect()->route('login')->with('error', 'Demo account not found.');
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
}
