<?php

namespace App\Http\Controllers;

use App\Models\UserGalleryImage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Illuminate\Support\Str;

class UserGalleryController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $imagesQuery = $request->user('user')->galleryImages();

        if ($search !== '') {
            $imagesQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('caption', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $imagesQuery->where('is_active', $status === 'active');
        }

        return view('users.gallery.index', [
            'images' => $imagesQuery->latest()->paginate(12)->withQueryString(),
            'user' => $request->user('user'),
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user('user');
        $path = $this->moveUploadedImage($request->file('image'), $user->id);

        $user->galleryImages()->create([
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? null,
            'image_path' => $path,
        ]);

        return redirect()->route('user.gallery.index')->with('status', 'Gallery image added successfully.');
    }

    public function update(Request $request, int $image)
    {
        $galleryImage = $this->findUserImage($request, $image);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldPath = $galleryImage->image_path;
        $newPath = $oldPath;
        if ($request->hasFile('image')) {
            $newPath = $this->moveUploadedImage($request->file('image'), $request->user('user')->id);
        }

        $galleryImage->update([
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? null,
            'image_path' => $newPath,
        ]);

        if ($newPath !== $oldPath) {
            $this->deleteUserImage($oldPath, $request->user('user')->id);
        }

        return redirect()->route('user.gallery.index')->with('status', 'Gallery image updated successfully.');
    }

    public function toggleStatus(Request $request, int $image)
    {
        $galleryImage = $this->findUserImage($request, $image);
        $galleryImage->is_active = ! $galleryImage->is_active;
        $galleryImage->save();

        return redirect()
            ->route('user.gallery.index')
            ->with('status', 'Gallery image '.($galleryImage->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $image)
    {
        $galleryImage = $this->findUserImage($request, $image);
        $this->deleteUserImage($galleryImage->image_path, $request->user('user')->id);
        $galleryImage->delete();

        return redirect()->route('user.gallery.index')->with('status', 'Gallery image deleted successfully.');
    }

    private function findUserImage(Request $request, int $image): UserGalleryImage
    {
        return $request->user('user')->galleryImages()->findOrFail($image);
    }

    private function moveUploadedImage(UploadedFile $image, int $userId): string
    {
        $directory = public_path((string) $userId);
        File::ensureDirectoryExists($directory, 0755, true);

        $filename = Str::uuid()->toString().'.'.$image->extension();
        $image->move($directory, $filename);

        return $userId.'/'.$filename;
    }

    private function deleteUserImage(string $path, int $userId): void
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $publicPrefix = $userId.'/';
        $legacyPrefix = 'user-gallery/'.$userId.'/';

        if (str_starts_with($normalizedPath, $publicPrefix)) {
            $relativePath = substr($normalizedPath, strlen($publicPrefix));
            if ($relativePath === '' || str_contains($relativePath, '..')) {
                throw new RuntimeException('The gallery image path is invalid.');
            }

            $deleted = File::delete(public_path($normalizedPath));
        } elseif (str_starts_with($normalizedPath, $legacyPrefix)) {
            $relativePath = substr($normalizedPath, strlen($legacyPrefix));
            if ($relativePath === '' || str_contains($relativePath, '..')) {
                throw new RuntimeException('The gallery image path is invalid.');
            }

            $deleted = Storage::disk('public')->delete($normalizedPath);
        } else {
            throw new RuntimeException('The gallery image does not belong to the current user folder.');
        }

        if (! $deleted) {
            throw new RuntimeException('The previous gallery image could not be removed.');
        }
    }
}
