<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserBankLink;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminBankLinkController extends Controller
{
    public function index(Request $request)
    {
        RoleAccess::assertAdmin($request->user());

        if (!Schema::hasTable('user_bank_links')) {
            return response()->json(['data' => []]);
        }

        $q = UserBankLink::with('user:id,name,email,phone,role')->latest();
        if ($request->filled('verified')) {
            $q->where('verified', filter_var($request->verified, FILTER_VALIDATE_BOOLEAN));
        }

        return response()->json(['data' => $q->paginate(30)]);
    }

    public function verify(Request $request, UserBankLink $userBankLink)
    {
        RoleAccess::assertAdmin($request->user());

        $userBankLink->update([
            'verified' => true,
            'meta' => array_merge($userBankLink->meta ?? [], [
                'verified_at' => now()->toIso8601String(),
                'verified_by' => $request->user()->id,
            ]),
        ]);

        return response()->json([
            'message' => 'Bank link verified',
            'link' => $userBankLink->fresh()->load('user:id,name,email'),
        ]);
    }

    public function unverify(Request $request, UserBankLink $userBankLink)
    {
        RoleAccess::assertAdmin($request->user());

        $userBankLink->update(['verified' => false]);

        return response()->json([
            'message' => 'Bank link marked unverified',
            'link' => $userBankLink->fresh(),
        ]);
    }
}
