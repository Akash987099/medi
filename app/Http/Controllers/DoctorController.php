<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Doctor;
use App\Models\DoctorLoginCredential;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    public function index()
    {
        return view('doctor.index', [
            'doctors' => Doctor::with(['category', 'hospital', 'user', 'loginCredential'])->latest()->paginate(10),
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'hospitals' => Hospital::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateDoctor($request);

        Doctor::create($validated + ['is_active' => true]);

        return redirect()
            ->route('doctor.index')
            ->with('status', 'Doctor registered successfully.');
    }

    public function update(Request $request, Doctor $doctor)
    {
        $validated = $this->validateDoctor($request, $doctor);
        DB::transaction(function () use ($doctor, $validated): void {
            $doctor->update($validated);
            if ($doctor->user) {
                $doctor->user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                ]);
            }
        });

        return redirect()
            ->route('doctor.index')
            ->with('status', 'Doctor details updated successfully.');
    }

    public function toggleStatus(Doctor $doctor)
    {
        $doctor->is_active = ! $doctor->is_active;
        $doctor->save();

        return redirect()
            ->route('doctor.index')
            ->with('status', 'Doctor '.($doctor->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function createLoginAccess(Doctor $doctor)
    {
        $doctor->loadMissing(['user', 'loginCredential']);

        if ($doctor->user) {
            if ($doctor->loginCredential && $doctor->loginCredential->user_id === $doctor->user_id) {
                return response()->json([
                    'message' => 'Login access has already been created for this doctor.',
                ], 409);
            }

            return response()->json([
                'message' => 'This account already exists without a saved credential. Use Reset login to create a new password.',
            ], 409);
        }

        if (User::where('email', $doctor->email)->exists()) {
            return response()->json([
                'message' => 'This email is already assigned to a user account.',
            ], 409);
        }

        $username = $this->generateUsername();

        $password = Str::random(16);

        try {
            DB::transaction(function () use ($doctor, $username, $password): void {
                $user = User::create([
                    'name' => $doctor->name,
                    'username' => $username,
                    'role' => 2,
                    'email' => $doctor->email,
                    'password' => $password,
                ]);

                DoctorLoginCredential::updateOrCreate(
                    ['doctor_id' => $doctor->id],
                    [
                        'user_id' => $user->id,
                        'username' => $username,
                        'password' => $password,
                    ],
                );

                $doctor->user()->associate($user);
                $doctor->save();
            });
        } catch (QueryException $exception) {
            $freshDoctor = $doctor->fresh(['user', 'loginCredential']);
            if (
                $freshDoctor->user
                && $freshDoctor->loginCredential
                && $freshDoctor->loginCredential->user_id === $freshDoctor->user_id
            ) {
                return response()->json([
                    'message' => 'Login access has already been created for this doctor.',
                ], 409);
            }

            throw $exception;
        }

        return response()->json([
            'message' => 'Login access created. The password is encrypted in storage and can be viewed again by an administrator.',
            'username' => $username,
            'password' => $password,
        ], 201);
    }

    public function viewLoginAccess(Doctor $doctor)
    {
        $doctor->load(['user', 'loginCredential']);

        if (!$doctor->user || !$doctor->loginCredential) {
            return response()->json([
                'message' => 'No saved login credentials are available for this doctor. Use Reset login to create a new password.',
            ], 409);
        }

        if ($doctor->loginCredential->user_id !== $doctor->user_id) {
            throw new \RuntimeException('Doctor login credentials do not match the linked user account.');
        }

        return response()->json([
            'message' => 'These are the existing login credentials. No password was generated or changed.',
            'username' => $doctor->loginCredential->username,
            'password' => $doctor->loginCredential->password,
        ]);
    }

    public function resetLoginAccess(Doctor $doctor)
    {
        $doctor->loadMissing('user');
        if (!$doctor->user) {
            return response()->json([
                'message' => 'Create login access for this doctor first.',
            ], 409);
        }

        $password = Str::random(20);

        DB::transaction(function () use ($doctor, $password): void {
            $doctor->user->password = $password;
            $doctor->user->role = 2;
            $doctor->user->save();

            DoctorLoginCredential::updateOrCreate(
                ['doctor_id' => $doctor->id],
                [
                    'user_id' => $doctor->user->id,
                    'username' => $doctor->user->username,
                    'password' => $password,
                ],
            );
        });

        return response()->json([
            'message' => 'A new password has been created and saved encrypted. The previous password no longer works.',
            'username' => $doctor->user->username,
            'password' => $password,
        ]);
    }

    private function generateUsername(): string
    {
        do {
            $username = Str::lower(Str::random(8));
        } while (User::where('username', $username)->exists());

        return $username;
    }

    private function validateDoctor(Request $request, ?Doctor $doctor = null): array
    {
        $uniqueEmail = Rule::unique('doctors', 'email');
        if ($doctor) {
            $uniqueEmail->ignore($doctor->id);
        }
        $emailRules = ['required', 'email', 'max:191', $uniqueEmail];
        if ($doctor?->user_id) {
            $emailRules[] = Rule::unique('users', 'email')->ignore($doctor->user_id);
        }

        return $request->validate([
            'category_id' => [
                'required',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'hospital_id' => ['nullable', 'integer', 'exists:hospitals,id'],
            'name' => ['required', 'string', 'max:120'],
            'email' => $emailRules,
            'phone' => ['required', 'string', 'max:30'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'qualification' => ['required', 'string', 'max:160'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:80'],
            'address' => ['nullable', 'string', 'max:2000'],
            'bio' => ['nullable', 'string', 'max:5000'],
        ], [
            'category_id.exists' => 'Select an active category.',
        ]);
    }
}
