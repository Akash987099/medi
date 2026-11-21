<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserPatientController extends Controller
{
    public function index(Request $request)
    {
        $user = $this->doctor($request);

        return view('users.patients.index', ['user' => $user, 'patients' => $user->patients()->latest()->get()]);
    }

    public function store(Request $request)
    {
        $this->doctor($request)->patients()->create($this->validatedPatient($request));
        return redirect()->route('user.patients.index')->with('status', 'Patient added successfully.');
    }

    public function update(Request $request, int $patient)
    {
        $this->doctor($request)->patients()->findOrFail($patient)->update($this->validatedPatient($request));
        return redirect()->route('user.patients.index')->with('status', 'Patient updated successfully.');
    }

    public function toggleStatus(Request $request, int $patient)
    {
        $item = $this->doctor($request)->patients()->findOrFail($patient);
        $item->update(['is_active' => ! $item->is_active]);
        return redirect()->route('user.patients.index')->with('status', 'Patient '.($item->is_active ? 'activated' : 'deactivated').' successfully.');
    }

    public function destroy(Request $request, int $patient)
    {
        $this->doctor($request)->patients()->findOrFail($patient)->delete();
        return redirect()->route('user.patients.index')->with('status', 'Patient deleted successfully.');
    }

    private function doctor(Request $request)
    {
        $user = $request->user('user');
        abort_unless($user->role === 2, 403);
        return $user;
    }

    private function validatedPatient(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'age' => ['nullable', 'integer', 'min:0', 'max:130'],
            'gender' => ['nullable', 'in:male,female,other,prefer_not_to_say'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
