<?php

namespace App\Http\Controllers;

use App\Models\UserFaq;
use Illuminate\Http\Request;

class UserFaqController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $faqsQuery = $request->user('user')->faqs();

        if ($search !== '') {
            $faqsQuery->where(function ($query) use ($search) {
                $query->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $faqsQuery->where('is_active', $status === 'active');
        }

        return view('users.faqs.index', [
            'faqs' => $faqsQuery->latest()->paginate(10)->withQueryString(),
            'user' => $request->user('user'),
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        $request->user('user')->faqs()->create($this->validateFaq($request));

        return redirect()->route('user.faqs.index')->with('status', 'FAQ added successfully.');
    }

    public function update(Request $request, int $faq)
    {
        $this->findUserFaq($request, $faq)->update($this->validateFaq($request));

        return redirect()->route('user.faqs.index')->with('status', 'FAQ updated successfully.');
    }

    public function toggleStatus(Request $request, int $faq)
    {
        $userFaq = $this->findUserFaq($request, $faq);
        $userFaq->is_active = ! $userFaq->is_active;
        $userFaq->save();

        return redirect()
            ->route('user.faqs.index')
            ->with('status', 'FAQ '.($userFaq->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    private function validateFaq(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:250'],
            'answer' => ['required', 'string', 'max:5000'],
        ]);
    }

    private function findUserFaq(Request $request, int $faq): UserFaq
    {
        return $request->user('user')->faqs()->findOrFail($faq);
    }
}
