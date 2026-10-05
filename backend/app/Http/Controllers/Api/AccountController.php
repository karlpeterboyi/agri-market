<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Farm;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $query = Account::with('parent', 'children')
            ->where(function ($q) use ($request) {
                // Organisation or farm scoped
                if ($request->filled('farm_id')) {
                    $q->where('farm_id', $request->farm_id);
                }
            });

        // Only show accounts belonging to farms owned by user (or admin)
        if (!in_array(auth()->user()->role ?? '', ['admin'])) {
            $query->whereHas('farm', fn ($q) => $q->where('owner_id', auth()->id()));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type); // asset, liability, equity, income, expense
        }

        return response()->json(
            $query->orderBy('code')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'nullable|exists:farms,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:accounts,id',
            'organisation_id' => 'nullable|exists:organisations,id',
        ]);

        if (!empty($validated['farm_id'])) {
            $farm = Farm::where('id', $validated['farm_id'])
                ->where('owner_id', auth()->id())
                ->firstOrFail();
            $validated['organisation_id'] = $farm->organisation_id;
        }

        $account = Account::create([
            ...$validated,
            'system' => false,
        ]);

        return response()->json($account->load('parent'), 201);
    }

    public function show(Account $account)
    {
        $this->authorizeAccess($account);

        return response()->json($account->load(['parent', 'children']));
    }

    public function update(Request $request, Account $account)
    {
        $this->authorizeAccess($account);

        if ($account->system) {
            return response()->json(['message' => 'System accounts cannot be modified.'], 422);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'parent_id' => 'nullable|exists:accounts,id',
        ]);

        $account->update($validated);

        return response()->json($account->fresh()->load('parent'));
    }

    public function destroy(Account $account)
    {
        $this->authorizeAccess($account);

        if ($account->system) {
            return response()->json(['message' => 'System accounts cannot be deleted.'], 422);
        }

        $account->delete();

        return response()->json(['message' => 'Account deleted.']);
    }

    protected function authorizeAccess(Account $account): void
    {
        if (in_array(auth()->user()->role ?? '', ['admin'])) {
            return;
        }

        if ($account->farm && $account->farm->owner_id !== auth()->id()) {
            abort(403);
        }
    }
}
