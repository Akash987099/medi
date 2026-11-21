<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserStaffController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('user');

        return view('users.staff.index', [
            'user' => $user,
            'staffMembers' => $user->staffMembers()->with('role')->latest()->get(),
            'roles' => $user->roles()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->user('user')->staffMembers()->create($this->validatedStaff($request));

        return redirect()->route('user.staff.index')->with('status', 'Staff member added successfully.');
    }

    public function update(Request $request, int $staff)
    {
        $item = $request->user('user')->staffMembers()->findOrFail($staff);
        $item->update($this->validatedStaff($request));

        return redirect()->route('user.staff.index')->with('status', 'Staff member updated successfully.');
    }

    public function toggleStatus(Request $request, int $staff)
    {
        $item = $request->user('user')->staffMembers()->findOrFail($staff);
        $item->update(['is_active' => ! $item->is_active]);

        return redirect()->route('user.staff.index')->with('status', 'Staff member '.($item->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $staff)
    {
        $request->user('user')->staffMembers()->findOrFail($staff)->delete();

        return redirect()->route('user.staff.index')->with('status', 'Staff member deleted successfully.');
    }

    private function validatedStaff(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'contact' => ['required', 'string', 'max:50'],
            'organization' => ['required', 'string', 'max:180'],
            'user_role_id' => [
                'nullable',
                'integer',
                Rule::exists('user_roles', 'id')->where(fn ($query) => $query
                    ->where('user_id', $request->user('user')->id)
                    ->where('is_active', true)),
            ],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
