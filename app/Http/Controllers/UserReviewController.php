<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserReview;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserReviewController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';

        $reviewsQuery = $request->user('user')->reviews();

        if ($search !== '') {
            $reviewsQuery->where(function ($query) use ($search) {
                $query->where('reviewer_name', 'like', "%{$search}%")
                    ->orWhere('review_text', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $reviewsQuery->where('is_active', $status === 'active');
        }

        $reviews = $reviewsQuery
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.reviews.index', [
            'reviews' => $reviews,
            'user' => $request->user('user'),
            'filters' => [
                'q' => $search,
                'status' => $status,
            ],
        ]);
    }

    public function qrPage(Request $request)
    {
        $user = $request->user('user');
        if (! $user->review_qr_token) {
            $user->review_qr_token = Str::random(64);
            $user->save();
        }

        $reviewUrl = route('public.reviews.create', $user->review_qr_token);
        $qrCode = (new Writer(new ImageRenderer(
            new RendererStyle(320, 4),
            new SvgImageBackEnd()
        )))->writeString($reviewUrl);
        $qrCode = preg_replace('/^<\?xml[^>]*>\s*/', '', $qrCode);

        return view('users.reviews.qr', [
            'user' => $user,
            'reviewUrl' => $reviewUrl,
            'qrCode' => $qrCode,
        ]);
    }

    public function publicForm(string $token)
    {
        $user = $this->findReviewAccount($token);

        return view('public-reviews.create', compact('user', 'token'));
    }

    public function publicStore(Request $request, string $token)
    {
        $user = $this->findReviewAccount($token);
        $validated = $request->validate([
            'reviewer_name' => ['required', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'review_text' => ['required', 'string', 'max:3000'],
        ]);

        $user->reviews()->create($validated);

        return redirect()
            ->route('public.reviews.create', $token)
            ->with('review_submitted', true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewer_name' => ['required', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'review_text' => ['required', 'string', 'max:3000'],
        ]);

        $request->user('user')->reviews()->create($validated);

        return redirect()
            ->route('user.reviews.index')
            ->with('status', 'Review added successfully.');
    }

    public function update(Request $request, int $review)
    {
        $validated = $request->validate([
            'reviewer_name' => ['required', 'string', 'max:120'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'review_text' => ['required', 'string', 'max:3000'],
        ]);

        $this->findUserReview($request, $review)->update($validated);

        return redirect()
            ->route('user.reviews.index')
            ->with('status', 'Review updated successfully.');
    }

    public function toggleStatus(Request $request, int $review)
    {
        $userReview = $this->findUserReview($request, $review);
        $userReview->is_active = ! $userReview->is_active;
        $userReview->save();

        return redirect()
            ->route('user.reviews.index')
            ->with('status', 'Review '.($userReview->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    private function findUserReview(Request $request, int $review): UserReview
    {
        return $request->user('user')->reviews()->findOrFail($review);
    }

    private function findReviewAccount(string $token): User
    {
        return User::query()
            ->where('review_qr_token', $token)
            ->whereIn('role', [1, 2])
            ->firstOrFail();
    }
}
