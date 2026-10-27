<?php

namespace App\Http\Controllers;

use App\Models\UserPackage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserPackageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('user');

        return view('users.packages.index', [
            'user' => $user,
            'packages' => $user->packages()->with('services')->latest()->get(),
            'services' => $user->services()
                ->whereNull('service_id')
                ->where('is_active', true)
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPackage($request);
        $serviceIds = $validated['service_ids'];
        unset($validated['service_ids']);

        $package = $request->user('user')->packages()->create($validated);
        $package->services()->sync($serviceIds);

        return redirect()->route('user.packages.index')->with('status', 'Package created successfully.');
    }

    public function update(Request $request, int $package)
    {
        $userPackage = $request->user('user')->packages()->findOrFail($package);
        $validated = $this->validatedPackage($request);
        $serviceIds = $validated['service_ids'];
        unset($validated['service_ids']);

        $userPackage->update($validated);
        $userPackage->services()->sync($serviceIds);

        return redirect()->route('user.packages.index')->with('status', 'Package updated successfully.');
    }

    public function toggleStatus(Request $request, int $package)
    {
        $userPackage = $request->user('user')->packages()->findOrFail($package);
        $userPackage->is_active = ! $userPackage->is_active;
        $userPackage->save();

        return redirect()
            ->route('user.packages.index')
            ->with('status', 'Package '.($userPackage->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $package)
    {
        $request->user('user')->packages()->findOrFail($package)->delete();

        return redirect()->route('user.packages.index')->with('status', 'Package deleted successfully.');
    }

    private function validatedPackage(Request $request): array
    {
        $userId = $request->user('user')->id;

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('user_services', 'id')
                    ->where(fn ($query) => $query
                        ->where('user_id', $userId)
                        ->whereNull('service_id')
                        ->where('is_active', true)),
            ],
        ]);
    }
}
