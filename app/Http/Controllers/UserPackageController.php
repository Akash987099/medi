<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Service;
use App\Models\UserPackage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserPackageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('user');

        // Catalog services are active for an account by default, even before the
        // account holder customizes one. Packages use user_services as their
        // selectable records, so make those available here as well.
        $categoryIds = $this->categoryIdsForUser($user->id, $user->role);
        $catalogServiceIds = Service::query()
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->pluck('id');

        if ($catalogServiceIds->isNotEmpty()) {
            $existingServiceIds = $user->services()
                ->whereIn('service_id', $catalogServiceIds)
                ->pluck('service_id');

            $missingServiceIds = $catalogServiceIds->diff($existingServiceIds);

            if ($missingServiceIds->isNotEmpty()) {
                $user->services()->createMany(
                    $missingServiceIds->map(fn ($serviceId) => [
                        'service_id' => $serviceId,
                        'is_active' => true,
                    ])->all()
                );
            }
        }

        return view('users.packages.index', [
            'user' => $user,
            'packages' => $user->packages()->with('services')->latest()->get(),
            'services' => $user->services()
                ->where('is_active', true)
                ->with('service')
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
                        ->where('is_active', true)),
            ],
        ]);
    }

    private function categoryIdsForUser(int $userId, int $role): array
    {
        if ($role === 1) {
            $hospitalId = Hospital::where('user_id', $userId)->value('id');

            if (! $hospitalId) {
                return [];
            }

            return Doctor::where('hospital_id', $hospitalId)
                ->distinct()
                ->pluck('category_id')
                ->all();
        }

        $categoryId = Doctor::where('user_id', $userId)->value('category_id');

        return $categoryId ? [$categoryId] : [];
    }
}
