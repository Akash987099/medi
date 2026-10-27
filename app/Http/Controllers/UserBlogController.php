<?php

namespace App\Http\Controllers;

use App\Models\UserBlog;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class UserBlogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $blogsQuery = $request->user('user')->blogs();

        if ($search !== '') {
            $blogsQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $blogsQuery->where('is_active', $status === 'active');
        }

        return view('users.blogs.index', [
            'blogs' => $blogsQuery->latest()->paginate(10)->withQueryString(),
            'user' => $request->user('user'),
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string', 'max:20000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user('user');
        $user->blogs()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $this->moveUploadedImage($request->file('image'), $user->id),
        ]);

        return redirect()->route('user.blogs.index')->with('status', 'Blog post added successfully.');
    }

    public function update(Request $request, int $blog)
    {
        $blogItem = $this->findUserBlog($request, $blog);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string', 'max:20000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldPath = $blogItem->image_path;
        $newPath = $request->hasFile('image')
            ? $this->moveUploadedImage($request->file('image'), $request->user('user')->id)
            : $oldPath;

        $blogItem->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $newPath,
        ]);

        if ($newPath !== $oldPath) {
            $this->deleteUserImage($oldPath, $request->user('user')->id);
        }

        return redirect()->route('user.blogs.index')->with('status', 'Blog post updated successfully.');
    }

    public function toggleStatus(Request $request, int $blog)
    {
        $blogItem = $this->findUserBlog($request, $blog);
        $blogItem->is_active = ! $blogItem->is_active;
        $blogItem->save();

        return redirect()
            ->route('user.blogs.index')
            ->with('status', 'Blog post '.($blogItem->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $blog)
    {
        $blogItem = $this->findUserBlog($request, $blog);
        $this->deleteUserImage($blogItem->image_path, $request->user('user')->id);
        $blogItem->delete();

        return redirect()->route('user.blogs.index')->with('status', 'Blog post deleted successfully.');
    }

    private function findUserBlog(Request $request, int $blog): UserBlog
    {
        return $request->user('user')->blogs()->findOrFail($blog);
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
            throw new RuntimeException('The blog image does not belong to the current user folder.');
        }

        $relativePath = substr($normalizedPath, strlen($prefix));
        if ($relativePath === '' || str_contains($relativePath, '..') || str_contains($relativePath, '/')) {
            throw new RuntimeException('The blog image path is invalid.');
        }

        if (! File::delete(public_path($normalizedPath))) {
            throw new RuntimeException('The previous blog image could not be removed.');
        }
    }
}
