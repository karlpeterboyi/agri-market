<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrganisationInvitationRequest;
use App\Http\Requests\AcceptOrganisationInvitationRequest;
use App\Models\Organisation;
use App\Models\OrganisationInvitation;
use App\Models\OrganisationMember;

class OrganisationInvitationController extends Controller
{
    public function store(
        StoreOrganisationInvitationRequest $request,
        Organisation $organisation
    ) {

        $invitation = OrganisationInvitation::create([

            'organisation_id' => $organisation->id,

            'invited_by' => auth()->id(),

            'email' => $request->email,

            'phone' => $request->phone,

            'role' => $request->role,

            'expires_at' => $request->expires_at ?? now()->addDays(7),

        ]);

        return response()->json($invitation, 201);
    }

    public function accept(
        AcceptOrganisationInvitationRequest $request
    ) {

        $invitation = OrganisationInvitation::where(
            'token',
            $request->token
        )->firstOrFail();

        if (
            $invitation->isExpired() ||
            $invitation->isAccepted()
        ) {
            return response()->json([
                'message' => 'Invitation is no longer valid.'
            ], 422);
        }

        OrganisationMember::firstOrCreate(
            [
                'organisation_id' => $invitation->organisation_id,
                'user_id' => auth()->id(),
            ],
            [
                'role' => $invitation->role,
                'joined_at' => now(),
            ]
        );

        $invitation->update([
            'accepted_at' => now(),
        ]);

        return response()->json([
            'message' => 'Organisation joined successfully.'
        ]);
    }
}