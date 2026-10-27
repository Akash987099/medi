<?php

namespace App\Http\Controllers;

use App\Models\UserSocialMediaLink;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserSocialMediaController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $linksQuery = $request->user('user')->socialMediaLinks();

        if ($search !== '') {
            $linksQuery->where(function ($query) use ($search) {
                $query->where('platform', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $linksQuery->where('is_active', $status === 'active');
        }

        return view('users.social-media.index', [
            'socialLinks' => $linksQuery->latest()->paginate(12)->withQueryString(),
            'user' => $request->user('user'),
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSocialLink($request);
        $request->user('user')->socialMediaLinks()->create($validated);

        return redirect()->route('user.social-media.index')->with('status', 'Social media link added successfully.');
    }

    public function update(Request $request, int $socialLink)
    {
        $link = $this->findUserLink($request, $socialLink);
        $link->update($this->validateSocialLink($request));

        return redirect()->route('user.social-media.index')->with('status', 'Social media link updated successfully.');
    }

    public function toggleStatus(Request $request, int $socialLink)
    {
        $link = $this->findUserLink($request, $socialLink);
        $link->is_active = ! $link->is_active;
        $link->save();

        return redirect()
            ->route('user.social-media.index')
            ->with('status', $link->platform.' link '.($link->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $socialLink)
    {
        $this->findUserLink($request, $socialLink)->delete();

        return redirect()->route('user.social-media.index')->with('status', 'Social media link deleted successfully.');
    }

    private function validateSocialLink(Request $request): array
    {
        return $request->validate([
            'platform' => ['required', 'string', 'max:80'],
            'url' => ['required', 'url', 'max:2048', 'regex:/^https:\\/\\//i'],
        ], [
            'url.regex' => 'Enter a secure HTTPS profile link.',
        ]);
    }

    private function findUserLink(Request $request, int $socialLink): UserSocialMediaLink
    {
        return $request->user('user')->socialMediaLinks()->findOrFail($socialLink);
    }
}
