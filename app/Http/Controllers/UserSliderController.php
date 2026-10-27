<?php

namespace App\Http\Controllers;

use App\Models\UserSliderItem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class UserSliderController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $slidesQuery = $request->user('user')->sliderItems();

        if ($search !== '') {
            $slidesQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('caption', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $slidesQuery->where('is_active', $status === 'active');
        }

        return view('users.slider.index', [
            'slides' => $slidesQuery->orderBy('sort_order')->latest('created_at')->paginate(12)->withQueryString(),
            'user' => $request->user('user'),
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $user = $request->user('user');
        $user->sliderItems()->create([
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? null,
            'sort_order' => $validated['sort_order'],
            'image_path' => $this->moveUploadedImage($request->file('image'), $user->id),
        ]);

        return redirect()->route('user.slider.index')->with('status', 'Slider image added successfully.');
    }

    public function update(Request $request, int $slide)
    {
        $sliderItem = $this->findUserSlide($request, $slide);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $oldPath = $sliderItem->image_path;
        $newPath = $request->hasFile('image')
            ? $this->moveUploadedImage($request->file('image'), $request->user('user')->id)
            : $oldPath;

        $sliderItem->update([
            'title' => $validated['title'],
            'caption' => $validated['caption'] ?? null,
            'sort_order' => $validated['sort_order'],
            'image_path' => $newPath,
        ]);

        if ($newPath !== $oldPath) {
            $this->deleteUserImage($oldPath, $request->user('user')->id);
        }

        return redirect()->route('user.slider.index')->with('status', 'Slider image updated successfully.');
    }

    public function toggleStatus(Request $request, int $slide)
    {
        $sliderItem = $this->findUserSlide($request, $slide);
        $sliderItem->is_active = ! $sliderItem->is_active;
        $sliderItem->save();

        return redirect()
            ->route('user.slider.index')
            ->with('status', 'Slider image '.($sliderItem->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $slide)
    {
        $sliderItem = $this->findUserSlide($request, $slide);
        $this->deleteUserImage($sliderItem->image_path, $request->user('user')->id);
        $sliderItem->delete();

        return redirect()->route('user.slider.index')->with('status', 'Slider image deleted successfully.');
    }

    private function findUserSlide(Request $request, int $slide): UserSliderItem
    {
        return $request->user('user')->sliderItems()->findOrFail($slide);
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
            throw new RuntimeException('The slider image does not belong to the current user folder.');
        }

        $relativePath = substr($normalizedPath, strlen($prefix));
        if ($relativePath === '' || str_contains($relativePath, '..') || str_contains($relativePath, '/')) {
            throw new RuntimeException('The slider image path is invalid.');
        }

        if (! File::delete(public_path($normalizedPath))) {
            throw new RuntimeException('The previous slider image could not be removed.');
        }
    }
}
