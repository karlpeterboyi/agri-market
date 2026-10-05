<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    protected function ensureAdmin(): void
    {
        if ((auth()->user()->role ?? '') !== 'admin') {
            abort(403, 'Admin only.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $q = User::query()->orderBy('id');

        if ($request->filled('role')) {
            $q->where('role', $request->role);
        }
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'ilike', "%{$s}%")
                    ->orWhere('email', 'ilike', "%{$s}%")
                    ->orWhere('phone', 'ilike', "%{$s}%");
            });
        }

        return response()->json($q->paginate($request->integer('per_page', 50)));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:255|unique:users,phone',
            'role' => ['required', Rule::in(['farmer', 'buyer', 'processor', 'provider', 'agrodealer', 'transporter', 'admin'])],
            'status' => 'nullable|in:active,inactive,suspended',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'role' => $data['role'],
            'status' => $data['status'] ?? 'active',
            'password' => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        return response()->json($user, 201);
    }

    public function show($id)
    {
        $this->ensureAdmin();
        return response()->json(User::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $this->ensureAdmin();
        $user = User::findOrFail($id);

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['sometimes', 'string', 'max:255', Rule::unique('users', 'phone')->ignore($user->id)],
            'role' => ['sometimes', Rule::in(['farmer', 'buyer', 'processor', 'provider', 'agrodealer', 'transporter', 'admin'])],
            'status' => 'sometimes|in:active,inactive,suspended',
            'password' => 'nullable|string|min:6',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json($user->fresh());
    }

    public function destroy($id)
    {
        $this->ensureAdmin();

        if ((int) $id === (int) auth()->id()) {
            return response()->json(['message' => 'Cannot delete your own account.'], 422);
        }

        User::findOrFail($id)->delete();

        return response()->json(['message' => 'User deleted.']);
    }
}
