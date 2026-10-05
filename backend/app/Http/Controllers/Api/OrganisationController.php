<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organisation;
use App\Models\OrganisationMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrganisationController extends Controller
{
    /**
     * List organisations available to the authenticated user.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Organisation::query()
            ->withCount([
                'members',
                'farms',
                'warehouses',
                'tasks',
                'workflows',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Only return organisations where the authenticated user is a member
        |--------------------------------------------------------------------------
        */

        $query->whereHas('members', function ($memberQuery) use ($user) {
            $memberQuery
                ->where('user_id', $user->id)
                ->where('active', true);
        });

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('active')) {
            $query->where(
                'active',
                filter_var(
                    $request->active,
                    FILTER_VALIDATE_BOOLEAN
                )
            );
        }

        if ($request->filled('verified')) {
            $query->where(
                'verified',
                filter_var(
                    $request->verified,
                    FILTER_VALIDATE_BOOLEAN
                )
            );
        }

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                    ->orWhere('registration_number', 'ILIKE', "%{$search}%")
                    ->orWhere('email', 'ILIKE', "%{$search}%")
                    ->orWhere('phone', 'ILIKE', "%{$search}%");
            });
        }

        $organisations = $query
            ->latest()
            ->paginate(
                $request->integer('per_page', 20)
            );

        return response()->json([
            'success' => true,
            'data' => $organisations,
        ]);
    }

    /**
     * Create a new organisation.
     *
     * The authenticated user automatically becomes the owner.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'type' => [
                'required',
                'string',
                'max:100',
            ],

            'registration_number' => [
                'nullable',
                'string',
                'max:255',
                'unique:organisations,registration_number',
            ],

            'tax_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'website' => [
                'nullable',
                'string',
                'max:255',
            ],

            'logo' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'region' => [
                'nullable',
                'string',
                'max:100',
            ],

            'district' => [
                'nullable',
                'string',
                'max:100',
            ],

            'ward' => [
                'nullable',
                'string',
                'max:100',
            ],

            'village' => [
                'nullable',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'settings' => [
                'nullable',
                'array',
            ],
        ]);

        $organisation = DB::transaction(function () use (
            $validated,
            $request
        ) {
            $organisation = Organisation::create([
                ...$validated,

                'uuid' => (string) Str::uuid(),

                'verified' => false,

                'active' => true,
            ]);

            OrganisationMember::create([
                'organisation_id' => $organisation->id,
                'user_id' => $request->user()->id,
                'role' => 'owner',
                'is_owner' => true,
                'active' => true,
                'joined_at' => now(),
            ]);

            return $organisation;
        });

        $organisation->load([
            'members.user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Organisation created successfully.',
            'data' => $organisation,
        ], 201);
    }

    /**
     * Show an organisation.
     */
    public function show(
        Request $request,
        Organisation $organisation
    ) {
        $this->authorizeOrganisationAccess(
            $request,
            $organisation
        );

        $organisation->load([
            'members.user',
            'farms',
            'warehouses',
            'tasks',
            'workflows',
        ]);

        return response()->json([
            'success' => true,
            'data' => $organisation,
        ]);
    }

    /**
     * Update an organisation.
     *
     * Only an organisation owner can update organisation details.
     */
    public function update(
        Request $request,
        Organisation $organisation
    ) {
        $this->authorizeOrganisationOwner(
            $request,
            $organisation
        );

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'type' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'registration_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(
                    'organisations',
                    'registration_number'
                )->ignore($organisation->id),
            ],

            'tax_number' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'website' => [
                'nullable',
                'string',
                'max:255',
            ],

            'logo' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'country' => [
                'nullable',
                'string',
                'max:100',
            ],

            'region' => [
                'nullable',
                'string',
                'max:100',
            ],

            'district' => [
                'nullable',
                'string',
                'max:100',
            ],

            'ward' => [
                'nullable',
                'string',
                'max:100',
            ],

            'village' => [
                'nullable',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'settings' => [
                'nullable',
                'array',
            ],

            'active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        $organisation->update($validated);

        $organisation->load([
            'members.user',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Organisation updated successfully.',
            'data' => $organisation,
        ]);
    }

    /**
     * Deactivate/delete an organisation.
     *
     * Because Organisation uses SoftDeletes, this performs a soft delete.
     */
    public function destroy(
        Request $request,
        Organisation $organisation
    ) {
        $this->authorizeOrganisationOwner(
            $request,
            $organisation
        );

        $organisation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Organisation deleted successfully.',
        ]);
    }

    /**
     * List organisation members.
     */
    public function members(
        Request $request,
        Organisation $organisation
    ) {
        $this->authorizeOrganisationAccess(
            $request,
            $organisation
        );

        $members = $organisation
            ->members()
            ->with('user')
            ->latest()
            ->paginate(
                $request->integer('per_page', 20)
            );

        return response()->json([
            'success' => true,
            'data' => $members,
        ]);
    }

    /**
     * Add a user to an organisation.
     */
    public function addMember(
        Request $request,
        Organisation $organisation
    ) {
        $this->authorizeOrganisationOwner(
            $request,
            $organisation
        );

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'role' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $existing = OrganisationMember::withTrashed()
            ->where('organisation_id', $organisation->id)
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existing) {
            if ($existing->active) {
                return response()->json([
                    'success' => false,
                    'message' =>
                        'This user is already a member of the organisation.',
                ], 422);
            }

            $existing->update([
                'role' => $validated['role'],
                'active' => true,
                'joined_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' =>
                    'Organisation membership reactivated successfully.',
                'data' => $existing->load('user'),
            ], 200);
        }

        $member = OrganisationMember::create([
            'organisation_id' =>
                $organisation->id,

            'user_id' =>
                $validated['user_id'],

            'role' =>
                $validated['role'],

            'is_owner' =>
                false,

            'active' =>
                true,

            'joined_at' =>
                now(),
        ]);

        $member->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Member added successfully.',
            'data' => $member,
        ], 201);
    }

    /**
     * Update an organisation member's role/status.
     */
    public function updateMember(
        Request $request,
        Organisation $organisation,
        OrganisationMember $member
    ) {
        $this->authorizeOrganisationOwner(
            $request,
            $organisation
        );

        $this->ensureMemberBelongsToOrganisation(
            $organisation,
            $member
        );

        $validated = $request->validate([
            'role' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'active' => [
                'sometimes',
                'boolean',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Do not allow the owner membership to be deactivated
        |--------------------------------------------------------------------------
        */

        if (
            $member->is_owner &&
            isset($validated['active']) &&
            !$validated['active']
        ) {
            return response()->json([
                'success' => false,
                'message' =>
                    'The organisation owner cannot be deactivated.',
            ], 422);
        }

        $member->update($validated);

        $member->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Organisation member updated successfully.',
            'data' => $member,
        ]);
    }

    /**
     * Remove a member from an organisation.
     *
     * The membership record is retained but deactivated so that
     * organisational history is not unnecessarily lost.
     */
    public function removeMember(
        Request $request,
        Organisation $organisation,
        OrganisationMember $member
    ) {
        $this->authorizeOrganisationOwner(
            $request,
            $organisation
        );

        $this->ensureMemberBelongsToOrganisation(
            $organisation,
            $member
        );

        if ($member->is_owner) {
            return response()->json([
                'success' => false,
                'message' =>
                    'The organisation owner cannot be removed.',
            ], 422);
        }

        $member->update([
            'active' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Organisation member removed successfully.',
        ]);
    }

    /**
     * Get organisation dashboard/summary.
     */
    public function dashboard(
        Request $request,
        Organisation $organisation
    ) {
        $this->authorizeOrganisationAccess(
            $request,
            $organisation
        );

        $organisation->loadCount([
            'members',
            'farms',
            'warehouses',
            'tasks',
            'workflows',
            'documents',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'organisation' => $organisation,

                'statistics' => [
                    'members' =>
                        $organisation->members_count,

                    'farms' =>
                        $organisation->farms_count,

                    'warehouses' =>
                        $organisation->warehouses_count,

                    'tasks' =>
                        $organisation->tasks_count,

                    'workflows' =>
                        $organisation->workflows_count,

                    'documents' =>
                        $organisation->documents_count,
                ],
            ],
        ]);
    }

    /**
     * Verify an organisation.
     *
     * This endpoint is intended for an authorised administrative process.
     */
    public function verify(
        Request $request,
        Organisation $organisation
    ) {
        /*
        |--------------------------------------------------------------------------
        | Do not expose organisation verification to ordinary members.
        |--------------------------------------------------------------------------
        |
        | This method intentionally checks the authenticated user's role.
        | Adjust the exact Spatie role name if your project uses another
        | administrator role.
        |
        */

        if (!$request->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' =>
                    'You are not authorised to verify organisations.',
            ], 403);
        }

        $organisation->update([
            'verified' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Organisation verified successfully.',
            'data' => $organisation,
        ]);
    }

    /**
     * Authorise ordinary organisation access.
     */
    protected function authorizeOrganisationAccess(
        Request $request,
        Organisation $organisation
    ): void {
        $isMember = $organisation
            ->members()
            ->where('user_id', $request->user()->id)
            ->where('active', true)
            ->exists();

        if (!$isMember) {
            abort(
                response()->json([
                    'success' => false,
                    'message' =>
                        'You do not have access to this organisation.',
                ], 403)
            );
        }
    }

    /**
     * Authorise organisation owner.
     */
    protected function authorizeOrganisationOwner(
        Request $request,
        Organisation $organisation
    ): void {
        $isOwner = $organisation
            ->members()
            ->where('user_id', $request->user()->id)
            ->where('is_owner', true)
            ->where('active', true)
            ->exists();

        if (!$isOwner) {
            abort(
                response()->json([
                    'success' => false,
                    'message' =>
                        'Only the organisation owner can perform this action.',
                ], 403)
            );
        }
    }

    /**
     * Ensure a member belongs to the specified organisation.
     */
    protected function ensureMemberBelongsToOrganisation(
        Organisation $organisation,
        OrganisationMember $member
    ): void {
        if (
            (int) $member->organisation_id !==
            (int) $organisation->id
        ) {
            abort(
                response()->json([
                    'success' => false,
                    'message' =>
                        'The specified member does not belong to this organisation.',
                ], 404)
            );
        }
    }
}