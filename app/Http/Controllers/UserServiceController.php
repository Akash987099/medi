<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Service;
use App\Models\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class UserServiceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('user');
        $categoryIds = $this->categoryIdsForUser($user->id, $user->role);
        $categories = Category::query()
            ->whereIn('id', $categoryIds)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $services = Service::with('category')
            ->whereIn('category_id', $categories->modelKeys())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $customServices = $user->services()
            ->whereNull('service_id')
            ->latest()
            ->get();
        $overrides = $user->services()
            ->whereIn('service_id', $services->modelKeys())
            ->get()
            ->keyBy('service_id');

        return view('users.services.index', [
            'user' => $user,
            'categories' => $categories,
            'services' => $services,
            'overrides' => $overrides,
            'customServices' => $customServices,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'content' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user('user');
        $user->services()->create([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'image_path' => $request->hasFile('image')
                ? $this->moveUploadedImage($request->file('image'), $user->id)
                : null,
            'is_active' => true,
        ]);

        return redirect()->route('user.services.index')->with('status', 'Your service was added successfully.');
    }

    public function updateCatalog(Request $request, int $service)
    {
        $adminService = $this->findAvailableCatalogService($request, $service);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'content' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user('user');
        $customization = $user->services()->firstOrNew(['service_id' => $adminService->id]);
        $oldPath = $customization->image_path;
        $newPath = $request->hasFile('image')
            ? $this->moveUploadedImage($request->file('image'), $user->id)
            : $oldPath;

        $customization->fill([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'image_path' => $newPath,
        ]);
        $customization->save();

        if ($newPath && $newPath !== $oldPath) {
            $this->deleteUserImage($oldPath, $user->id);
        }

        return redirect()->route('user.services.index')->with('status', 'Your service content was updated.');
    }

    public function toggleCatalogStatus(Request $request, int $service)
    {
        $adminService = $this->findAvailableCatalogService($request, $service);
        $customization = $request->user('user')->services()->firstOrNew(['service_id' => $adminService->id]);
        $customization->is_active = ! $customization->exists || ! $customization->is_active;
        $customization->save();

        return redirect()
            ->route('user.services.index')
            ->with('status', 'Service '.($customization->is_active ? 'activated' : 'deactivated').' for your account.');
    }

    public function updateCustom(Request $request, int $userService)
    {
        $customService = $this->findCustomService($request, $userService);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'content' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldPath = $customService->image_path;
        $newPath = $request->hasFile('image')
            ? $this->moveUploadedImage($request->file('image'), $request->user('user')->id)
            : $oldPath;

        $customService->update([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'image_path' => $newPath,
        ]);

        if ($newPath && $newPath !== $oldPath) {
            $this->deleteUserImage($oldPath, $request->user('user')->id);
        }

        return redirect()->route('user.services.index')->with('status', 'Your service was updated successfully.');
    }

    public function toggleCustomStatus(Request $request, int $userService)
    {
        $customService = $this->findCustomService($request, $userService);
        $customService->is_active = ! $customService->is_active;
        $customService->save();

        return redirect()
            ->route('user.services.index')
            ->with('status', 'Service '.($customService->is_active ? 'activated' : 'deactivated').' for your account.');
    }

    public function destroyCustom(Request $request, int $userService)
    {
        $customService = $this->findCustomService($request, $userService);
        if ($customService->image_path) {
            $this->deleteUserImage($customService->image_path, $request->user('user')->id);
        }
        $customService->delete();

        return redirect()->route('user.services.index')->with('status', 'Your service was deleted successfully.');
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

    private function findAvailableCatalogService(Request $request, int $service): Service
    {
        $user = $request->user('user');
        $categoryIds = Category::query()
            ->whereIn('id', $this->categoryIdsForUser($user->id, $user->role))
            ->where('is_active', true)
            ->pluck('id');

        return Service::whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->findOrFail($service);
    }

    private function findCustomService(Request $request, int $userService): UserService
    {
        return $request->user('user')->services()
            ->whereNull('service_id')
            ->findOrFail($userService);
    }

    private function moveUploadedImage(UploadedFile $image, int $userId): string
    {
        $directory = public_path((string) $userId);
        File::ensureDirectoryExists($directory, 0755, true);

        $filename = Str::uuid()->toString().'.'.$image->extension();
        $image->move($directory, $filename);

        return $userId.'/'.$filename;
    }

    private function deleteUserImage(?string $path, int $userId): void
    {
        if (! $path) {
            return;
        }

        $normalizedPath = str_replace('\\', '/', $path);
        $prefix = $userId.'/';
        if (! str_starts_with($normalizedPath, $prefix)) {
            throw new RuntimeException('The service image does not belong to the current user folder.');
        }

        $relativePath = substr($normalizedPath, strlen($prefix));
        if ($relativePath === '' || str_contains($relativePath, '..') || str_contains($relativePath, '/')) {
            throw new RuntimeException('The service image path is invalid.');
        }

        if (! File::delete(public_path($normalizedPath))) {
            throw new RuntimeException('The previous service image could not be removed.');
        }
    }
}
