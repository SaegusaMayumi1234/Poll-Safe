<?php

namespace App\Http\Controllers\organization;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\organization\AddOrganizationRequest;
use App\Http\Requests\organization\EditOrganizationRequest;
use App\Http\Requests\organization\DeleteOrganizationRequest;

class OrganizationActionController extends Controller
{
    // request: name + description
    public function addOrganization(AddOrganizationRequest $request) {
        if (isset($request->validator) && $request->validator->fails()) {
            return back()->withErrors([
                'error' => $request->validator->errors()->first(),
            ])->withInput();
        }

        $validated = $request->validated();

        $organization = Organization::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => Auth::id(),
            'role' => 1,
        ]);

        return redirect()->route('dashboard');
    }
    // request: name + description + organization id
    public function editOrganization(EditOrganizationRequest $request) {
        if (isset($request->validator) && $request->validator->fails()) {
            return back()->withErrors([
                'error' => $request->validator->errors()->first(),
            ])->withInput();
        }

        $validated = $request->validated();

        $organization = Organization::findOrFail($validated['organization_id']);
        
        $role = OrganizationMember::where('organization_id', $validated['organization_id'])
            ->where('user_id', Auth::id())
            ->first()
            ->role;

        if ($role !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $organization->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
        ]);

        return redirect()->route('dashboard');
    }

    public function deleteOrganization(DeleteOrganizationRequest $request) {
        // $userId = Auth::user()->id
        // $organizationId = organization id

        // $organization = OrganizationMember::where(organization_id, $organizationId)->and(user_id, $userId)->and(role_id, Leader);

        if (isset($request->validator) && $request->validator->fails()) {
            return back()->withErrors([
                'error' => $request->validator->errors()->first(),
            ])->withInput();
        }

        $validated = $request->validated();

        $organization = Organization::findOrFail($validated['organization_id']);
        
        $role = OrganizationMember::where('organization_id', $validated['organization_id'])
            ->where('user_id', Auth::id())
            ->first()
            ->role;

        if ($role !== 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $organization->where('id', $organization->id)->delete();
        return redirect()->route('dashboard');
    }
}