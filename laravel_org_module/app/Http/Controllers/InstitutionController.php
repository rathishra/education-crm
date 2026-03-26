<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Organization;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function index(Request $request)
    {
        $query = Institution::with('organization');

        if ($request->filled('organization_id')) {
            $query->where('organization_id', $request->organization_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('institution_name', 'like', "%{$search}%")
                  ->orWhere('institution_code', 'like', "%{$search}%");
            });
        }

        $institutions = $query->withCount('departments')
                              ->orderBy('institution_name')
                              ->paginate(15);
                              
        $organizations = Organization::where('status', 'active')->get();

        return view('institutions.index', compact('institutions', 'organizations'));
    }

    public function create()
    {
        $organizations = Organization::where('status', 'active')->get();
        return view('institutions.create', compact('organizations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'institution_name' => 'required|string|max:255',
            'institution_code' => 'required|string|unique:institutions,institution_code',
            'institution_type' => 'nullable|string',
            'email' => 'nullable|email',
            'status' => 'required|in:active,inactive',
        ]);

        // Enforcement of max_institutions can be done here or in a Request validation custom rule.
        $organization = Organization::findOrFail($request->organization_id);
        if ($organization->institutions()->count() >= $organization->max_institutions) {
            return back()->withInput()->withErrors(['organization_id' => 'This organization has reached its limit of institutions.']);
        }

        Institution::create($validated);

        return redirect()->route('institutions.index')
                         ->with('success', 'Institution created successfully.');
    }

    public function edit(Institution $institution)
    {
        $organizations = Organization::where('status', 'active')->get();
        return view('institutions.edit', compact('institution', 'organizations'));
    }

    public function update(Request $request, Institution $institution)
    {
        $validated = $request->validate([
            'organization_id' => 'required|exists:organizations,id',
            'institution_name' => 'required|string|max:255',
            'institution_type' => 'nullable|string',
            'email' => 'nullable|email',
            'status' => 'required|in:active,inactive',
        ]);

        $institution->update($validated);

        return redirect()->route('institutions.index')
                         ->with('success', 'Institution updated successfully.');
    }
}
