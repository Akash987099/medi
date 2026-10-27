<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\HospitalLoginCredential;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class HospitalController extends Controller
{
    public function index()
    {
        return view('hospital.index', [
            'hospitals' => Hospital::with(['user', 'loginCredential'])->latest()->paginate(10),
        ]);
    }

    public function store(Request $request)
    {
        Hospital::create($this->validateHospital($request) + ['is_active' => true]);

        return redirect()
            ->route('hospital.index')
            ->with('status', 'Hospital registered successfully.');
    }

    public function update(Request $request, Hospital $hospital)
    {
        $validated = $this->validateHospital($request, $hospital);

        DB::transaction(function () use ($hospital, $validated): void {
            $hospital->update($validated);
            if ($hospital->user) {
                $hospital->user->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                ]);
            }
        });

        return redirect()
            ->route('hospital.index')
            ->with('status', 'Hospital details updated successfully.');
    }

    public function toggleStatus(Hospital $hospital)
    {
        $hospital->is_active = ! $hospital->is_active;
        $hospital->save();

        return redirect()
            ->route('hospital.index')
            ->with('status', 'Hospital '.($hospital->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function createLoginAccess(Hospital $hospital)
    {
        $hospital->loadMissing(['user', 'loginCredential']);

        if ($hospital->user) {
            if ($hospital->loginCredential && $hospital->loginCredential->user_id === $hospital->user_id) {
                return response()->json([
                    'message' => 'Login access has already been created for this hospital.',
                ], 409);
            }

            return response()->json([
                'message' => 'This account already exists without a saved credential. Use Reset login to create a new password.',
            ], 409);
        }

        if (User::where('email', $hospital->email)->exists()) {
            return response()->json([
                'message' => 'This email is already assigned to a user account.',
            ], 409);
        }

        $username = $this->generateUsername();
        $password = Str::random(16);

        try {
            DB::transaction(function () use ($hospital, $username, $password): void {
                $user = User::create([
                    'name' => $hospital->name,
                    'username' => $username,
                    'role' => 1,
                    'email' => $hospital->email,
                    'password' => $password,
                ]);

                HospitalLoginCredential::updateOrCreate(
                    ['hospital_id' => $hospital->id],
                    [
                        'user_id' => $user->id,
                        'username' => $username,
                        'password' => $password,
                    ],
                );

                $hospital->user()->associate($user);
                $hospital->save();
            });
        } catch (QueryException $exception) {
            $freshHospital = $hospital->fresh(['user', 'loginCredential']);
            if (
                $freshHospital->user
                && $freshHospital->loginCredential
                && $freshHospital->loginCredential->user_id === $freshHospital->user_id
            ) {
                return response()->json([
                    'message' => 'Login access has already been created for this hospital.',
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

    public function viewLoginAccess(Hospital $hospital)
    {
        $hospital->load(['user', 'loginCredential']);

        if (!$hospital->user || !$hospital->loginCredential) {
            return response()->json([
                'message' => 'No saved login credentials are available for this hospital. Use Reset login to create a new password.',
            ], 409);
        }

        if ($hospital->loginCredential->user_id !== $hospital->user_id) {
            throw new \RuntimeException('Hospital login credentials do not match the linked user account.');
        }

        return response()->json([
            'message' => 'These are the existing login credentials. No password was generated or changed.',
            'username' => $hospital->loginCredential->username,
            'password' => $hospital->loginCredential->password,
        ]);
    }

    public function resetLoginAccess(Hospital $hospital)
    {
        $hospital->loadMissing('user');
        if (!$hospital->user) {
            return response()->json([
                'message' => 'Create login access for this hospital first.',
            ], 409);
        }

        $password = Str::random(20);

        DB::transaction(function () use ($hospital, $password): void {
            $hospital->user->password = $password;
            $hospital->user->role = 1;
            $hospital->user->save();

            HospitalLoginCredential::updateOrCreate(
                ['hospital_id' => $hospital->id],
                [
                    'user_id' => $hospital->user->id,
                    'username' => $hospital->user->username,
                    'password' => $password,
                ],
            );
        });

        return response()->json([
            'message' => 'A new password has been created and saved encrypted. The previous password no longer works.',
            'username' => $hospital->user->username,
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

    private function validateHospital(Request $request, ?Hospital $hospital = null): array
    {
        $uniqueEmail = Rule::unique('hospitals', 'email');
        if ($hospital) {
            $uniqueEmail->ignore($hospital->id);
        }

        $emailRules = ['required', 'email', 'max:191', $uniqueEmail];
        if ($hospital?->user_id) {
            $emailRules[] = Rule::unique('users', 'email')->ignore($hospital->user_id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => $emailRules,
            'phone' => ['required', 'string', 'max:30'],
            'website_url' => ['nullable', 'url', 'max:2048'],
            'address' => ['nullable', 'string', 'max:2000'],
            'bio' => ['nullable', 'string', 'max:5000'],
        ]);
    }
}
