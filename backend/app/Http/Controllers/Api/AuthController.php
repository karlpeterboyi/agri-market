<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OrganisationMember;
use App\Models\User;
use App\Models\UserBankLink;
use App\Models\Wallet;
use App\Support\RegistrationRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function registrationRoles()
    {
        return response()->json([
            'roles' => array_values(RegistrationRoles::publicRoles()),
        ]);
    }

    public function register(Request $request)
    {
        try {
            $roleCodes = RegistrationRoles::codes();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:30|unique:users,phone',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|confirmed|min:6',
                'role' => ['required', Rule::in($roleCodes)],
                'business_name' => 'nullable|string|max:255',
                'region' => 'nullable|string|max:100',
                'district' => 'nullable|string|max:100',
                'tin' => 'nullable|string|max:50',
                'nmb_account_number' => 'nullable|string|max:64',
                'nmb_account_name' => 'nullable|string|max:255',
                'accept_terms' => 'nullable|boolean',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first() ?: 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $def = RegistrationRoles::definition($validated['role']) ?? [];
        // Prefer active so users can log in; financier/educator can be reviewed later
        $status = 'active';
        if (($def['verification'] ?? '') === 'pending') {
            $status = 'pending';
        }

        try {
            // Plain password — User model casts 'password' => 'hashed'
            $user = User::create([
                'name' => $validated['name'],
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'role' => $validated['role'],
                'status' => $status,
            ]);
        } catch (\Throwable $e) {
            Log::error('Register user failed', ['error' => $e->getMessage()]);
            $msg = $e->getMessage();

            // Retry without pending status if status check blocks
            if (str_contains($msg, 'status') && $status === 'pending') {
                try {
                    $user = User::create([
                        'name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'email' => $validated['email'],
                        'password' => $validated['password'],
                        'role' => $validated['role'],
                        'status' => 'active',
                    ]);
                    $status = 'active';
                } catch (\Throwable $e2) {
                    return $this->registerFailResponse($e2);
                }
            } else {
                return $this->registerFailResponse($e);
            }
        }

        // Wallet (if boot missed or failed)
        try {
            if (class_exists(Wallet::class) && Schema::hasTable('wallets')) {
                Wallet::firstOrCreate(
                    ['user_id' => $user->id],
                    ['available_balance' => 0, 'pending_balance' => 0]
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Register wallet skipped', ['error' => $e->getMessage()]);
        }

        if (!empty($def['needs_organisation'])) {
            $this->tryCreateOrganisation($user, $validated, $def);
        }

        if (!empty($validated['nmb_account_number'])) {
            $this->tryLinkNmb($user, $validated);
        }

        if ($status === 'pending') {
            return response()->json([
                'message' => 'Registration received. Account pending verification before login.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                ],
                'home' => $def['home'] ?? '/',
                'token' => null,
                'requires_activation' => true,
            ], 201);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user->fresh(),
            'token' => $token,
            'home' => $def['home'] ?? '/',
            'nmb_link_recommended' => (bool) ($def['nmb_link_recommended'] ?? false),
            'requires_activation' => false,
        ], 201);
    }

    protected function registerFailResponse(\Throwable $e)
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'users_role_check') || (str_contains($msg, 'role') && str_contains($msg, 'check'))) {
            return response()->json([
                'message' => 'This role is not enabled on the database yet. Run: php artisan migrate',
                'error' => $msg,
                'hint' => 'Allowed roles must match users_role_check. Migration expands farmer,buyer,processor,provider,agrodealer,transporter,financier,educator,admin.',
            ], 422);
        }

        if (str_contains($msg, 'users_email_unique') || str_contains($msg, 'email')) {
            return response()->json(['message' => 'Email already registered.', 'error' => $msg], 422);
        }

        if (str_contains($msg, 'users_phone_unique') || str_contains($msg, 'phone')) {
            return response()->json(['message' => 'Phone already registered.', 'error' => $msg], 422);
        }

        return response()->json([
            'message' => 'Registration failed while creating user.',
            'error' => $msg,
        ], 500);
    }

    protected function tryCreateOrganisation(User $user, array $validated, array $def): void
    {
        try {
            if (!class_exists(Organisation::class) || !Schema::hasTable('organisations')) {
                return;
            }

            // Must match organisations.type enum
            $typeMap = [
                'farm' => 'individual',
                'trading' => 'company',
                'processor' => 'processor',
                'agrodealer' => 'input_supplier',
                'service' => 'individual',
                'transport' => 'logistics_provider',
                'financial_institution' => 'financial_institution',
                'research' => 'research',
                'individual' => 'individual',
            ];
            $type = $typeMap[$def['organisation_type'] ?? 'individual'] ?? 'individual';
            $name = $validated['business_name'] ?? ($user->name."'s organisation");
            $slug = Str::slug($name).'-'.Str::lower(Str::random(4));

            $organisation = Organisation::create([
                'uuid' => (string) Str::uuid(),
                'name' => $name,
                'slug' => $slug,
                'type' => $type,
                'email' => $user->email,
                'phone' => $user->phone,
                'region' => $validated['region'] ?? null,
                'district' => $validated['district'] ?? null,
                'active' => true,
            ]);

            if (class_exists(OrganisationMember::class) && Schema::hasTable('organisation_members')) {
                OrganisationMember::create([
                    'organisation_id' => $organisation->id,
                    'user_id' => $user->id,
                    'role' => 'owner',
                    'is_owner' => true,
                    'joined_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Register organisation skipped', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function tryLinkNmb(User $user, array $validated): void
    {
        try {
            if (!class_exists(UserBankLink::class) || !Schema::hasTable('user_bank_links')) {
                return;
            }

            UserBankLink::create([
                'user_id' => $user->id,
                'provider' => 'nmb',
                'bank_code' => 'NMB',
                'account_number' => $validated['nmb_account_number'],
                'account_name' => $validated['nmb_account_name'] ?? $user->name,
                'verified' => false,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Register NMB link skipped', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (($user->status ?? 'active') === 'pending') {
            return response()->json([
                'message' => 'Account is pending verification. You will be notified once approved.',
            ], 403);
        }

        if (($user->status ?? 'active') !== 'active') {
            return response()->json([
                'message' => 'Account is not active. Contact support.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $def = RegistrationRoles::definition($user->role);

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
            'home' => $def['home'] ?? null,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function profile(Request $request)
    {
        return response()->json($request->user());
    }
}
