<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\User;
use App\Models\UserAppointment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['all', ...UserAppointment::STATUSES])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $search = trim($filters['q'] ?? '');
        $status = $filters['status'] ?? 'all';
        $user = $request->user('user');
        $appointmentsQuery = $user->appointments()->with('doctor');

        if ($search !== '') {
            $appointmentsQuery->where(function (Builder $query) use ($search) {
                $query->where('patient_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            $appointmentsQuery->where('status', $status);
        }

        if (! empty($filters['date_from'])) {
            $appointmentsQuery->whereDate('appointment_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $appointmentsQuery->whereDate('appointment_at', '<=', $filters['date_to']);
        }

        $appointments = $appointmentsQuery
            ->orderByRaw('CASE WHEN appointment_at >= ? THEN 0 ELSE 1 END', [now()])
            ->orderBy('appointment_at')
            ->paginate(12)
            ->withQueryString();

        $counts = $user->appointments()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status IN ('scheduled', 'confirmed') AND appointment_at >= ? THEN 1 ELSE 0 END) as upcoming", [now()])
            ->selectRaw("SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed")
            ->first();

        $doctors = collect();
        if ($user->role === 1) {
            $hospital = Hospital::where('user_id', $user->id)->firstOrFail();
            $doctors = $hospital->doctors()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'qualification']);
        }

        return view('users.appointments.index', [
            'user' => $user,
            'appointments' => $appointments,
            'doctors' => $doctors,
            'filters' => [
                'q' => $search,
                'status' => $status,
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
            ],
            'counts' => $counts,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user('user');
        $validated = $this->validateAppointment($request, $user->role === 1);
        $validated['status'] = 'scheduled';

        $this->saveWithoutConflict($user->id, $validated);

        return redirect()
            ->route('user.appointments.index')
            ->with('status', 'Appointment scheduled successfully.');
    }

    public function update(Request $request, int $appointment)
    {
        $user = $request->user('user');
        $appointmentRecord = $user->appointments()->findOrFail($appointment);
        $validated = $this->validateAppointment($request, $user->role === 1, true, $appointmentRecord);

        $this->saveWithoutConflict($user->id, $validated, $appointmentRecord);

        return redirect()
            ->route('user.appointments.index')
            ->with('status', 'Appointment updated successfully.');
    }

    public function destroy(Request $request, int $appointment)
    {
        $request->user('user')->appointments()->findOrFail($appointment)->delete();

        return redirect()
            ->route('user.appointments.index')
            ->with('status', 'Appointment deleted successfully.');
    }

    private function validateAppointment(
        Request $request,
        bool $isHospital,
        bool $canSetStatus = false,
        ?UserAppointment $appointment = null
    ): array
    {
        $user = $request->user('user');
        $doctorIdRule = ['nullable', 'integer'];
        $doctorId = null;

        if ($isHospital) {
            $hospital = Hospital::where('user_id', $user->id)->firstOrFail();
            $doctorIdRule[] = Rule::exists('doctors', 'id')
                ->where('hospital_id', $hospital->id)
                ->where('is_active', true);
        } else {
            $doctorId = Doctor::where('user_id', $user->id)->value('id');
            $doctorIdRule = ['prohibited'];
        }

        $rules = [
            'doctor_id' => $doctorIdRule,
            'patient_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:191'],
            'appointment_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:240'],
            'reason' => ['required', 'string', 'max:250'],
            'notes' => ['nullable', 'string', 'max:3000'],
        ];

        if ($canSetStatus) {
            $rules['status'] = ['required', Rule::in(UserAppointment::STATUSES)];
        }

        $validated = $request->validate($rules);

        if (! $isHospital && $doctorId) {
            $validated['doctor_id'] = $doctorId;
        }

        $validated['appointment_at'] = Carbon::createFromFormat('Y-m-d\TH:i', $validated['appointment_at']);

        if ($validated['appointment_at']->lte(now())
            && (! $appointment || $validated['appointment_at']->format('Y-m-d\TH:i')
                !== $appointment->appointment_at->format('Y-m-d\TH:i'))) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'appointment_at' => 'Choose a date and time in the future.',
            ]);
        }

        return $validated;
    }

    private function saveWithoutConflict(int $userId, array $validated, ?UserAppointment $appointment = null): void
    {
        DB::transaction(function () use ($userId, $validated, $appointment): void {
            $user = $appointment?->user()->lockForUpdate()->first()
                ?? User::query()->lockForUpdate()->findOrFail($userId);
            $start = Carbon::parse($validated['appointment_at']);
            $end = $start->copy()->addMinutes((int) $validated['duration_minutes']);
            $status = $validated['status'] ?? 'scheduled';

            if (in_array($status, ['scheduled', 'confirmed'], true)) {
                $conflictQuery = $user->appointments()
                    ->whereIn('status', ['scheduled', 'confirmed'])
                    ->where('appointment_at', '>=', $start->copy()->subMinutes(240))
                    ->where('appointment_at', '<', $end);

                if ($appointment) {
                    $conflictQuery->where('id', '<>', $appointment->id);
                }

                if ($validated['doctor_id'] ?? null) {
                    $conflictQuery->where('doctor_id', $validated['doctor_id']);
                } elseif ($user->role === 1) {
                    $conflictQuery->whereNull('doctor_id');
                }

                $hasConflict = $conflictQuery->get(['appointment_at', 'duration_minutes'])
                    ->contains(fn (UserAppointment $existing) => $existing->appointment_at
                        ->copy()
                        ->addMinutes($existing->duration_minutes)
                        ->gt($start));

                if ($hasConflict) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'appointment_at' => 'This time overlaps another scheduled or confirmed appointment.',
                    ]);
                }
            }

            if ($appointment) {
                $appointment->update($validated);
            } else {
                $user->appointments()->create($validated);
            }
        });
    }
}
