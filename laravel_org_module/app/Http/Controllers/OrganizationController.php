<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Http\Requests\StoreOrganizationRequest;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = Organization::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('organization_name', 'like', "%{$search}%")
                  ->orWhere('organization_code', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $organizations = $query->withCount('institutions')
                               ->orderBy('organization_name')
                               ->paginate(15);

        return view('organizations.index', compact('organizations'));
    }

    public function create()
    {
        return view('organizations.create');
    }

    public function store(StoreOrganizationRequest $request)
    {
        $organization = Organization::create($request->validated());
        
        // Log activity
        activity_log($organization->id, 'super_admin', auth()->id(), 'created', 'organization', 'Created new UI organization');

        return redirect()->route('organizations.index')
                         ->with('success', 'Organization created successfully.');
    }

    public function edit(Organization $organization)
    {
        return view('organizations.edit', compact('organization'));
    }

    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
            'max_institutions' => 'required|integer|min:1'
        ]);

        $organization->update($validated);

        return redirect()->route('organizations.index')
                         ->with('success', 'Organization updated successfully.');
    }

    public function destroy(Organization $organization)
    {
        $organization->delete(); // Soft delete
        return redirect()->route('organizations.index')
                         ->with('success', 'Organization deactivated/deleted successfully.');
    }
}
