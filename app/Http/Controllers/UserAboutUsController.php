<?php

namespace App\Http\Controllers;

use App\Models\UserAboutUsPage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class UserAboutUsController extends Controller
{
    public function index(Request $request)
    {
        return view('users.about-us.index', [
            'user' => $request->user('user'),
            'aboutPage' => $request->user('user')->aboutUsPage,
        ]);
    }

    public function save(Request $request)
    {
        $user = $request->user('user');
        $validated = $request->validate([
            'hero_title' => ['required', 'string', 'max:200'],
            'intro' => ['nullable', 'string', 'max:2000'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'sections' => ['nullable', 'array', 'max:20'],
            'sections.*.key' => ['required', 'string', 'max:50'],
            'sections.*.title' => ['required', 'string', 'max:160'],
            'sections.*.content' => ['required', 'string', 'max:10000'],
            'sections.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $aboutPage = $user->aboutUsPage()->firstOrNew();
        $oldHeroImage = $aboutPage->hero_image_path;
        $oldSections = collect($aboutPage->sections ?? [])->keyBy('key');
        $oldImagePaths = collect($aboutPage->sections ?? [])
            ->pluck('image_path')
            ->push($oldHeroImage)
            ->filter()
            ->unique();
        $newHeroImage = $request->hasFile('hero_image')
            ? $this->moveUploadedImage($request->file('hero_image'), $user->id)
            : $oldHeroImage;
        $newSections = [];

        foreach ($validated['sections'] ?? [] as $index => $section) {
            $oldSection = $oldSections->get($section['key']);
            $imagePath = $request->file("sections.{$index}.image")
                ? $this->moveUploadedImage($request->file("sections.{$index}.image"), $user->id)
                : ($oldSection['image_path'] ?? null);

            $newSections[] = [
                'key' => $section['key'],
                'title' => $section['title'],
                'content' => $section['content'],
                'image_path' => $imagePath,
            ];
        }

        $aboutPage->fill([
            'hero_title' => $validated['hero_title'],
            'intro' => $validated['intro'] ?? null,
            'hero_image_path' => $newHeroImage,
            'sections' => $newSections,
        ]);
        $aboutPage->user()->associate($user);
        $aboutPage->save();

        $retainedImages = collect($newSections)
            ->pluck('image_path')
            ->filter()
            ->push($newHeroImage)
            ->all();
        $oldImagePaths = $oldImagePaths->reject(fn ($path) => in_array($path, $retainedImages, true));

        foreach ($oldImagePaths as $oldPath) {
            $this->deleteUserImage($oldPath, $user->id);
        }

        return redirect()->route('user.about-us.index')->with('status', 'About Us page saved successfully.');
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
        if ($path === null || $path === '') {
            return;
        }

        $normalizedPath = str_replace('\\', '/', $path);
        $prefix = $userId.'/';
        if (! str_starts_with($normalizedPath, $prefix)) {
            throw new RuntimeException('The About Us image does not belong to the current user folder.');
        }

        $relativePath = substr($normalizedPath, strlen($prefix));
        if ($relativePath === '' || str_contains($relativePath, '..') || str_contains($relativePath, '/')) {
            throw new RuntimeException('The About Us image path is invalid.');
        }

        if (! File::delete(public_path($normalizedPath))) {
            throw new RuntimeException('The previous About Us image could not be removed.');
        }
    }
}
