<?php

namespace App\Http\Controllers;

use App\Models\UserWebsiteSetting;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

class UserWebsiteSettingsController extends Controller
{
    public function index(Request $request)
    {
        return view('users.website-settings.index', [
            'user' => $request->user('user'),
            'settings' => $request->user('user')->websiteSettings,
        ]);
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:160'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'address' => ['nullable', 'string', 'max:2000'],
            'footer_text' => ['nullable', 'string', 'max:2000'],
            'copyright_text' => ['nullable', 'string', 'max:255'],
            'header_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'footer_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user('user');
        $settings = $user->websiteSettings()->firstOrNew();
        $oldHeaderLogo = $settings->header_logo_path;
        $oldFooterLogo = $settings->footer_logo_path;
        $newHeaderLogo = $request->hasFile('header_logo')
            ? $this->moveUploadedLogo($request->file('header_logo'), $user->id)
            : $oldHeaderLogo;
        $newFooterLogo = $request->hasFile('footer_logo')
            ? $this->moveUploadedLogo($request->file('footer_logo'), $user->id)
            : $oldFooterLogo;

        $settings->fill([
            'site_name' => $validated['site_name'],
            'tagline' => $validated['tagline'] ?? null,
            'contact_email' => $validated['contact_email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'website_url' => $validated['website_url'] ?? null,
            'address' => $validated['address'] ?? null,
            'footer_text' => $validated['footer_text'] ?? null,
            'copyright_text' => $validated['copyright_text'] ?? null,
            'header_logo_path' => $newHeaderLogo,
            'footer_logo_path' => $newFooterLogo,
        ]);
        $settings->user()->associate($user);
        $settings->save();

        if ($newHeaderLogo !== $oldHeaderLogo) {
            $this->deleteUserLogo($oldHeaderLogo, $user->id);
        }
        if ($newFooterLogo !== $oldFooterLogo) {
            $this->deleteUserLogo($oldFooterLogo, $user->id);
        }

        return redirect()->route('user.website-settings.index')->with('status', 'Website settings saved successfully.');
    }

    private function moveUploadedLogo(UploadedFile $logo, int $userId): string
    {
        $directory = public_path((string) $userId);
        File::ensureDirectoryExists($directory, 0755, true);

        $filename = Str::uuid()->toString().'.'.$logo->extension();
        $logo->move($directory, $filename);

        return $userId.'/'.$filename;
    }

    private function deleteUserLogo(?string $path, int $userId): void
    {
        if ($path === null || $path === '') {
            return;
        }

        $normalizedPath = str_replace('\\', '/', $path);
        $prefix = $userId.'/';
        if (! str_starts_with($normalizedPath, $prefix)) {
            throw new RuntimeException('The website logo does not belong to the current user folder.');
        }

        $relativePath = substr($normalizedPath, strlen($prefix));
        if ($relativePath === '' || str_contains($relativePath, '..') || str_contains($relativePath, '/')) {
            throw new RuntimeException('The website logo path is invalid.');
        }

        if (! File::delete(public_path($normalizedPath))) {
            throw new RuntimeException('The previous website logo could not be removed.');
        }
    }
}
