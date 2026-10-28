<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Organization;
use App\Models\OrganizationMember;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'company_name' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'avatar_url' => 'https://api.dicebear.com/7.x/initials/svg?seed=' . urlencode($validated['name']),
            'timezone' => 'Asia/Kolkata',
        ]);

        // Create initial organization for the user
        $companyName = !empty($validated['company_name']) ? $validated['company_name'] : ($validated['name'] . "'s Company");
        $slug = Str::slug($companyName) . '-' . Str::random(4);

        $org = Organization::create([
            'name' => $companyName,
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

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'job_title' => $user->job_title,
                'bio' => $user->bio,
                'timezone' => $user->timezone,
            ],
            'organization' => [
                'id' => (string) $org->id,
                'name' => $org->name,
                'slug' => $org->slug,
                'role' => 'OWNER',
            ],
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $orgMemberships = OrganizationMember::with('organization')
            ->where('user_id', $user->id)
            ->get();

        $organizations = $orgMemberships->map(function ($m) {
            return [
                'id' => (string) $m->organization->id,
                'name' => $m->organization->name,
                'slug' => $m->organization->slug,
                'role' => $m->role,
                'department' => $m->department,
                'client_company' => $m->client_company,
            ];
        });

        $activeOrg = $organizations->first();

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'job_title' => $user->job_title,
                'bio' => $user->bio,
                'timezone' => $user->timezone,
            ],
            'organizations' => $organizations,
            'organization' => $activeOrg,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        $orgMemberships = OrganizationMember::with('organization')
            ->where('user_id', $user->id)
            ->get();

        $organizations = $orgMemberships->map(function ($m) {
            return [
                'id' => (string) $m->organization->id,
                'name' => $m->organization->name,
                'slug' => $m->organization->slug,
                'role' => $m->role,
                'department' => $m->department,
                'client_company' => $m->client_company,
            ];
        });

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => (string) $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url,
                'job_title' => $user->job_title,
                'bio' => $user->bio,
                'timezone' => $user->timezone,
            ],
            'organizations' => $organizations,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully.',
        ]);
    }
}

