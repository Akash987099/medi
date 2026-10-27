<?php

namespace App\Http\Controllers;

use App\Models\UserCmsPage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;

class UserCmsPageController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $pagesQuery = $request->user('user')->cmsPages();

        if ($search !== '') {
            $pagesQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $pagesQuery->where('is_active', $status === 'active');
        }

        return view('users.cms-pages.index', [
            'pages' => $pagesQuery->latest()->paginate(10)->withQueryString(),
            'user' => $request->user('user'),
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user('user');
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:180', 'alpha_dash'],
            'content' => ['required', 'string', 'max:50000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user->cmsPages()->create([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($user->id, $validated['slug'] ?: $validated['title']),
            'content' => $validated['content'],
            'image_path' => $request->hasFile('image')
                ? $this->moveUploadedImage($request->file('image'), $user->id)
                : null,
        ]);

        return redirect()->route('user.cms-pages.index')->with('status', 'CMS page created successfully.');
    }

    public function update(Request $request, int $page)
    {
        $user = $request->user('user');
        $cmsPage = $user->cmsPages()->findOrFail($page);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'nullable',
                'string',
                'max:180',
                'alpha_dash',
                Rule::unique('user_cms_pages', 'slug')
                    ->where(fn ($query) => $query->where('user_id', $user->id))
                    ->ignore($cmsPage->id),
            ],
            'content' => ['required', 'string', 'max:50000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldPath = $cmsPage->image_path;
        $newPath = $request->hasFile('image')
            ? $this->moveUploadedImage($request->file('image'), $user->id)
            : $oldPath;

        $cmsPage->update([
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($user->id, $validated['slug'] ?: $validated['title'], $cmsPage->id),
            'content' => $validated['content'],
            'image_path' => $newPath,
        ]);

        if ($newPath && $newPath !== $oldPath) {
            $this->deleteUserImage($oldPath, $user->id);
        }

        return redirect()->route('user.cms-pages.index')->with('status', 'CMS page updated successfully.');
    }

    public function toggleStatus(Request $request, int $page)
    {
        $cmsPage = $request->user('user')->cmsPages()->findOrFail($page);
        $cmsPage->is_active = ! $cmsPage->is_active;
        $cmsPage->save();

        return redirect()
            ->route('user.cms-pages.index')
            ->with('status', 'CMS page '.($cmsPage->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $page)
    {
        $cmsPage = $request->user('user')->cmsPages()->findOrFail($page);
        if ($cmsPage->image_path) {
            $this->deleteUserImage($cmsPage->image_path, $request->user('user')->id);
        }
        $cmsPage->delete();

        return redirect()->route('user.cms-pages.index')->with('status', 'CMS page deleted successfully.');
    }

    private function uniqueSlug(int $userId, string $value, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($value) ?: 'page';
        $slug = $baseSlug;
        $suffix = 2;

        while (UserCmsPage::where('user_id', $userId)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
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
        $prefix = $userId.'/';
        if (! str_starts_with($normalizedPath, $prefix)) {
            throw new RuntimeException('The CMS page image does not belong to the current user folder.');
        }

        $relativePath = substr($normalizedPath, strlen($prefix));
        if ($relativePath === '' || str_contains($relativePath, '..') || str_contains($relativePath, '/')) {
            throw new RuntimeException('The CMS page image path is invalid.');
        }

        if (! File::delete(public_path($normalizedPath))) {
            throw new RuntimeException('The previous CMS page image could not be removed.');
        }
    }
}
