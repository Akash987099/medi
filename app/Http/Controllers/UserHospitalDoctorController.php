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
        $validated = $this->validatedDoctor($request);

        $hospital->doctors()->create($validated + ['is_active' => true]);

        return redirect()
            ->route('user.doctors.index')
            ->with('status', 'Doctor added to your hospital successfully.');
    }

    public function update(Request $request, int $doctor)
    {
        abort_unless($request->user('user')->role === 1, 403);

        $hospital = Hospital::where('user_id', $request->user('user')->id)->firstOrFail();
        $doctor = $hospital->doctors()->findOrFail($doctor);
        $validated = $this->validatedDoctor($request, $doctor);
        $doctor->update($validated);

        if ($doctor->user) {
            $doctor->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);
        }

        return redirect()
            ->route('user.doctors.index')
            ->with('status', 'Doctor details updated successfully.');
    }

    private function validatedDoctor(Request $request, ?Doctor $doctor = null): array
    {
        $doctorEmail = Rule::unique('doctors', 'email');
        $userEmail = Rule::unique('users', 'email');
        if ($doctor) {
            $doctorEmail->ignore($doctor->id);
            if ($doctor->user_id) {
                $userEmail->ignore($doctor->user_id);
            }
        }

        return $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:191', $doctorEmail, $userEmail],
            'phone' => ['required', 'string', 'max:30'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'qualification' => ['required', 'string', 'max:160'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i', 'after:opening_time'],
            'address' => ['nullable', 'string', 'max:2000'],
            'bio' => ['nullable', 'string', 'max:5000'],
        ], [
            'category_id.exists' => 'Select an active category.',
        ], [
            'closing_time.after' => 'Closing time must be after opening time.',
        ]);
    }
}
