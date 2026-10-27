<?php

namespace App\Http\Controllers;

use App\Models\UserArticle;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class UserArticleController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $articlesQuery = $request->user('user')->articles();

        if ($search !== '') {
            $articlesQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $articlesQuery->where('is_active', $status === 'active');
        }

        return view('users.articles.index', [
            'articles' => $articlesQuery->latest()->paginate(10)->withQueryString(),
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
        $user->articles()->create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $this->moveUploadedImage($request->file('image'), $user->id),
        ]);

        return redirect()->route('user.articles.index')->with('status', 'Article added successfully.');
    }

    public function update(Request $request, int $article)
    {
        $articleItem = $this->findUserArticle($request, $article);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'content' => ['required', 'string', 'max:20000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldPath = $articleItem->image_path;
        $newPath = $request->hasFile('image')
            ? $this->moveUploadedImage($request->file('image'), $request->user('user')->id)
            : $oldPath;

        $articleItem->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_path' => $newPath,
        ]);

        if ($newPath !== $oldPath) {
            $this->deleteUserImage($oldPath, $request->user('user')->id);
        }

        return redirect()->route('user.articles.index')->with('status', 'Article updated successfully.');
    }

    public function toggleStatus(Request $request, int $article)
    {
        $articleItem = $this->findUserArticle($request, $article);
        $articleItem->is_active = ! $articleItem->is_active;
        $articleItem->save();

        return redirect()
            ->route('user.articles.index')
            ->with('status', 'Article '.($articleItem->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $article)
    {
        $articleItem = $this->findUserArticle($request, $article);
        $this->deleteUserImage($articleItem->image_path, $request->user('user')->id);
        $articleItem->delete();

        return redirect()->route('user.articles.index')->with('status', 'Article deleted successfully.');
    }

    private function findUserArticle(Request $request, int $article): UserArticle
    {
        return $request->user('user')->articles()->findOrFail($article);
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
        $legacyPrefix = 'user-articles/'.$userId.'/';

        if (str_starts_with($normalizedPath, $publicPrefix)) {
            $relativePath = substr($normalizedPath, strlen($publicPrefix));
            if ($relativePath === '' || str_contains($relativePath, '..')) {
                throw new RuntimeException('The article image path is invalid.');
            }

            $deleted = File::delete(public_path($normalizedPath));
        } elseif (str_starts_with($normalizedPath, $legacyPrefix)) {
            $relativePath = substr($normalizedPath, strlen($legacyPrefix));
            if ($relativePath === '' || str_contains($relativePath, '..')) {
                throw new RuntimeException('The article image path is invalid.');
            }

            $deleted = Storage::disk('public')->delete($normalizedPath);
        } else {
            throw new RuntimeException('The article image does not belong to the current user folder.');
        }

        if (! $deleted) {
            throw new RuntimeException('The previous article image could not be removed.');
        }
    }
}
