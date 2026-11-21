<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('user');

        return view('users.roles.index', [
            'user' => $user,
            'roles' => $user->roles()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->user('user')->roles()->create($this->validatedRole($request));

        return redirect()->route('user.roles.index')->with('status', 'Role created successfully.');
    }

    public function update(Request $request, int $role)
    {
        $item = $request->user('user')->roles()->findOrFail($role);
        $item->update($this->validatedRole($request, $item->id));

        return redirect()->route('user.roles.index')->with('status', 'Role updated successfully.');
    }

    public function toggleStatus(Request $request, int $role)
    {
        $item = $request->user('user')->roles()->findOrFail($role);
        $item->update(['is_active' => ! $item->is_active]);

        return redirect()->route('user.roles.index')->with('status', 'Role '.($item->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $role)
    {
        $request->user('user')->roles()->findOrFail($role)->delete();

        return redirect()->route('user.roles.index')->with('status', 'Role deleted successfully.');
    }

    private function validatedRole(Request $request, ?int $ignoreRole = null): array
    {
        return $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('user_roles', 'name')
                    ->where('user_id', $request->user('user')->id)
                    ->ignore($ignoreRole),
            ],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
