<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Doctor;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserHospitalDoctorController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user('user')->role === 1, 403);

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:all,active,inactive'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $hospital = Hospital::where('user_id', $request->user('user')->id)->firstOrFail();
        $doctorsQuery = $hospital->doctors()->with('category');

        if ($search !== '') {
            $doctorsQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('qualification', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($status !== 'all') {
            $doctorsQuery->where('is_active', $status === 'active');
        }

        return view('users.doctors.index', [
            'user' => $request->user('user'),
            'hospital' => $hospital,
            'doctors' => $doctorsQuery->latest()->paginate(12)->withQueryString(),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'filters' => ['q' => $search, 'status' => $status],
        ]);
    }

    public function store(Request $request)
    {
        abort_unless($request->user('user')->role === 1, 403);

        $hospital = Hospital::where('user_id', $request->user('user')->id)->firstOrFail();
        $validated = $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:191', 'unique:doctors,email', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:30'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'qualification' => ['required', 'string', 'max:160'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'address' => ['nullable', 'string', 'max:2000'],
            'bio' => ['nullable', 'string', 'max:5000'],
        ], [
            'category_id.exists' => 'Select an active category.',
        ]);

        $hospital->doctors()->create($validated + ['is_active' => true]);

        return redirect()
            ->route('user.doctors.index')
            ->with('status', 'Doctor added to your hospital successfully.');
    }
}
