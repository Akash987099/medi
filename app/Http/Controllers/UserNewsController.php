<?php

namespace App\Http\Controllers;

use App\Models\UserNews;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class UserNewsController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $newsQuery = $request->user('user')->newsItems();

        if ($search !== '') {
            $newsQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $newsQuery->where('is_active', $status === 'active');
        }

        return view('users.news.index', [
            'newsItems' => $newsQuery->latest()->paginate(10)->withQueryString(),
            'user' => $request->user('user'),
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'content' => ['required', 'string', 'max:10000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user('user');
        $user->newsItems()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $this->moveUploadedImage($request->file('image'), $user->id),
        ]);

        return redirect()->route('user.news.index')->with('status', 'News article added successfully.');
    }

    public function update(Request $request, int $news)
    {
        $newsItem = $this->findUserNews($request, $news);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'content' => ['required', 'string', 'max:10000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldPath = $newsItem->image_path;
        $newPath = $request->hasFile('image')
            ? $this->moveUploadedImage($request->file('image'), $request->user('user')->id)
            : $oldPath;

        $newsItem->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $newPath,
        ]);

        if ($newPath !== $oldPath) {
            $this->deleteUserImage($oldPath, $request->user('user')->id);
        }

        return redirect()->route('user.news.index')->with('status', 'News article updated successfully.');
    }

    public function toggleStatus(Request $request, int $news)
    {
        $newsItem = $this->findUserNews($request, $news);
        $newsItem->is_active = ! $newsItem->is_active;
        $newsItem->save();

        return redirect()
            ->route('user.news.index')
            ->with('status', 'News article '.($newsItem->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $news)
    {
        $newsItem = $this->findUserNews($request, $news);
        $this->deleteUserImage($newsItem->image_path, $request->user('user')->id);
        $newsItem->delete();

        return redirect()->route('user.news.index')->with('status', 'News article deleted successfully.');
    }

    private function findUserNews(Request $request, int $news): UserNews
    {
        return $request->user('user')->newsItems()->findOrFail($news);
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
        $legacyPrefix = 'user-news/'.$userId.'/';

        if (str_starts_with($normalizedPath, $publicPrefix)) {
            $relativePath = substr($normalizedPath, strlen($publicPrefix));
            if ($relativePath === '' || str_contains($relativePath, '..')) {
                throw new RuntimeException('The news image path is invalid.');
            }

            $deleted = File::delete(public_path($normalizedPath));
        } elseif (str_starts_with($normalizedPath, $legacyPrefix)) {
            $relativePath = substr($normalizedPath, strlen($legacyPrefix));
            if ($relativePath === '' || str_contains($relativePath, '..')) {
                throw new RuntimeException('The news image path is invalid.');
            }

            $deleted = Storage::disk('public')->delete($normalizedPath);
        } else {
            throw new RuntimeException('The news image does not belong to the current user folder.');
        }

        if (! $deleted) {
            throw new RuntimeException('The previous news image could not be removed.');
        }
    }
}
