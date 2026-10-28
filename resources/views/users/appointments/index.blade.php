@extends('users.layout.app')

@section('title', 'Appointments')

@section('content')
<style>
  .appointment-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
  .appointment-stat { padding: 16px 18px; background: var(--white); border: 1px solid var(--line); border-radius: 10px; }
  .appointment-stat span { display: block; color: var(--muted); font-size: 10px; font-weight: 600; }
  .appointment-stat strong { display: block; margin-top: 8px; color: var(--green-dark); font-family: 'Playfair Display', serif; font-size: 25px; font-weight: 600; }
  .appointment-layout { display: grid; grid-template-columns: minmax(285px, .75fr) minmax(0, 1.65fr); align-items: start; gap: 16px; }
  .appointment-form { display: grid; gap: 13px; padding: 0 20px 20px; }
  .appointment-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 11px; }
  .appointment-field { display: grid; gap: 6px; min-width: 0; }
  .appointment-field.wide { grid-column: 1 / -1; }
  .appointment-field label { color: #647064; font-size: 10px; font-weight: 700; }
  .appointment-field input, .appointment-field select, .appointment-field textarea { width: 100%; min-height: 39px; padding: 9px 10px; color: var(--ink); background: #fff; border: 1px solid #e6e7de; border-radius: 7px; outline: none; font: inherit; font-size: 11px; }
  .appointment-field input[type="datetime-local"] { min-width: 0; padding-right: 7px; color-scheme: light; appearance: auto; }
  .appointment-field input[type="datetime-local"]::-webkit-calendar-picker-indicator { display: block; width: 16px; height: 16px; margin-left: 3px; opacity: 1; cursor: pointer; }
  .appointment-field textarea { min-height: 76px; resize: vertical; }
  .appointment-field input:focus, .appointment-field select:focus, .appointment-field textarea:focus { border-color: #91a58f; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .appointment-help { margin: -4px 0 0; color: #92998f; font-size: 9px; line-height: 1.5; }
  .appointment-primary, .appointment-filter-button { min-height: 39px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 14px; color: #fff; background: var(--green-dark); border: 1px solid var(--green-dark); border-radius: 7px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .appointment-primary:hover, .appointment-filter-button:hover { background: #253e30; }
  .appointment-alert { margin-bottom: 15px; padding: 12px 14px; border: 1px solid #dce8d9; border-radius: 8px; color: #4c6a50; background: #eff5ed; font-size: 11px; }
  .appointment-alert.error { color: #8d5544; background: #fbefeb; border-color: #f0d9ce; }
  .appointment-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; padding: 0 20px 16px; }
  .appointment-toolbar input, .appointment-toolbar select { min-height: 36px; padding: 8px 10px; color: var(--ink); background: #fff; border: 1px solid #e6e7de; border-radius: 7px; font: inherit; font-size: 10px; }
  .appointment-toolbar input[type="search"] { flex: 1 1 160px; }
  .appointment-filter-button { min-height: 36px; padding: 0 12px; font-size: 10px; }
  .appointment-reset { color: var(--green); font-size: 10px; font-weight: 700; }
  .appointment-list { padding: 0 14px 14px; }
  .appointment-card { padding: 15px; margin-top: 10px; background: #fff; border: 1px solid #eeeee7; border-radius: 9px; }
  .appointment-card:first-child { margin-top: 0; }
  .appointment-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
  .appointment-patient { min-width: 0; display: flex; align-items: center; gap: 11px; }
  .appointment-avatar { width: 38px; height: 38px; flex: 0 0 38px; display: grid; place-items: center; color: #5b765f; background: #eaf0e7; border-radius: 50%; font-family: 'Playfair Display', serif; font-size: 14px; font-weight: 700; }
  .appointment-patient-copy { min-width: 0; display: grid; gap: 4px; }
  .appointment-patient-copy strong { color: #3c4b40; font-size: 12px; }
  .appointment-patient-copy a, .appointment-patient-copy span { overflow: hidden; color: #92998f; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
  .appointment-badge { display: inline-flex; align-items: center; padding: 5px 9px; border-radius: 20px; font-size: 9px; font-weight: 700; white-space: nowrap; }
  .appointment-badge.scheduled { color: #927344; background: #f8f1e3; }
  .appointment-badge.confirmed { color: #557655; background: #edf3eb; }
  .appointment-badge.completed { color: #536f78; background: #eaf1f2; }
  .appointment-badge.cancelled, .appointment-badge.no_show { color: #976653; background: #f7ece6; }
  .appointment-meta { display: flex; flex-wrap: wrap; gap: 7px 15px; margin: 13px 0 0 49px; color: #737f74; font-size: 10px; }
  .appointment-meta span { display: inline-flex; align-items: center; gap: 6px; }
  .appointment-meta i { color: #819282; }
  .appointment-reason { margin: 11px 0 0 49px; color: #69756a; font-size: 10px; line-height: 1.55; }
  .appointment-note { margin: 8px 0 0 49px; padding: 8px 10px; color: #858d83; background: #faf9f5; border-radius: 6px; font-size: 10px; line-height: 1.55; white-space: pre-line; }
  .appointment-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 7px; margin-top: 13px; }
  .appointment-edit-toggle, .appointment-delete { min-height: 31px; display: inline-flex; align-items: center; gap: 6px; padding: 0 10px; border: 1px solid #e8e9e1; border-radius: 6px; color: #667466; background: #fff; cursor: pointer; font: inherit; font-size: 10px; font-weight: 600; }
  .appointment-edit-toggle:hover { color: var(--green-dark); background: #f3f6f1; }
  .appointment-delete { color: #a36d58; }
  .appointment-delete:hover { background: #fbefeb; }
  .appointment-edit { margin-top: 12px; border-top: 1px solid #efefe9; }
  .appointment-edit summary { width: max-content; padding: 10px 0 0; color: var(--green); cursor: pointer; font-size: 10px; font-weight: 700; list-style: none; }
  .appointment-edit summary::-webkit-details-marker { display: none; }
  .appointment-edit[open] summary { margin-bottom: 12px; }
  .appointment-edit .appointment-form { padding: 0 0 2px; }
  .appointment-empty { padding: 42px 18px; color: #92998f; text-align: center; font-size: 11px; }
  .appointment-empty i { display: block; margin-bottom: 10px; color: #aab6a7; font-size: 25px; }
  .appointment-pagination { padding: 0 18px 18px; }
  .appointment-pagination nav { display: flex; justify-content: space-between; }
  @media (max-width: 1080px) { .appointment-layout { grid-template-columns: 1fr; } }
  @media (max-width: 760px) { .appointment-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } .appointment-toolbar input[type="date"] { flex: 1 1 120px; } }
  @media (max-width: 480px) { .appointment-fields { grid-template-columns: 1fr; } .appointment-field.wide { grid-column: auto; } .appointment-card-top { flex-direction: column; } .appointment-meta, .appointment-reason, .appointment-note { margin-left: 0; } }
</style>

<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Appointments</div>
    </div>
    <div class="topbar-actions">
      <div class="admin-profile">
        <div class="avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</div>
        <div class="profile-copy">
          <strong>{{ $user->name }}</strong>
          <span>{{ $user->role === 1 ? 'Hospital account' : 'Doctor account' }}</span>
        </div>
      </div>
    </div>
  </header>

  <div class="content-scroll">
    <div class="content-wrap">
      <section class="page-heading" aria-labelledby="appointments-title">
        <div>
          <p class="eyebrow">Patient scheduling</p>
          <h1 id="appointments-title">Appointments</h1>
          <p class="page-subtitle">Schedule visits, keep patient details together, and track each appointment through completion.</p>
        </div>
        <div class="date-chip"><i class="far fa-calendar"></i>{{ now()->format('l, F j, Y') }}</div>
      </section>

      @if (session('status'))
        <div class="appointment-alert" role="status">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="appointment-alert error" role="alert">
          <strong>Please review the following:</strong>
          <ul style="margin:7px 0 0;padding-left:18px">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <section class="appointment-stats" aria-label="Appointment summary">
        <article class="appointment-stat"><span>Total appointments</span><strong>{{ $counts->total ?? 0 }}</strong></article>
        <article class="appointment-stat"><span>Upcoming visits</span><strong>{{ $counts->upcoming ?? 0 }}</strong></article>
        <article class="appointment-stat"><span>Scheduled</span><strong>{{ $counts->scheduled ?? 0 }}</strong></article>
        <article class="appointment-stat"><span>Completed</span><strong>{{ $counts->completed ?? 0 }}</strong></article>
      </section>

      <div class="appointment-layout">
        <section class="panel" aria-labelledby="add-appointment-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="add-appointment-title">Schedule an appointment</h2>
              <p class="panel-caption">Add a new patient visit to your workspace</p>
            </div>
          </div>
          <form class="appointment-form" method="POST" action="{{ route('user.appointments.store') }}">
            @csrf
            <div class="appointment-fields">
              <div class="appointment-field wide">
                <label for="patient_name">Patient name</label>
                <input id="patient_name" name="patient_name" value="{{ old('patient_name') }}" maxlength="120" autocomplete="name" required>
              </div>
              <div class="appointment-field">
                <label for="phone">Phone number</label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="30" autocomplete="tel" required>
              </div>
              <div class="appointment-field">
                <label for="email">Email <span style="font-weight:400">(optional)</span></label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="191" autocomplete="email">
              </div>
              <div class="appointment-field">
                <label for="appointment_at">Date and time</label>
                <input id="appointment_at" name="appointment_at" type="datetime-local" value="{{ old('appointment_at', now()->addHour()->format('Y-m-d\TH:i')) }}" min="{{ now()->addMinute()->startOfMinute()->format('Y-m-d\TH:i') }}" required>
              </div>
              <div class="appointment-field">
                <label for="duration_minutes">Duration</label>
                <select id="duration_minutes" name="duration_minutes" required>
                  @foreach ([15, 20, 30, 45, 60, 90, 120, 180, 240] as $duration)
                    <option value="{{ $duration }}" @selected((int) old('duration_minutes', 30) === $duration)>{{ $duration }} minutes</option>
                  @endforeach
                </select>
              </div>
              @if ($user->role === 1)
                <div class="appointment-field wide">
                  <label for="doctor_id">Doctor <span style="font-weight:400">(optional)</span></label>
                  <select id="doctor_id" name="doctor_id">
                    <option value="">Not assigned to a doctor</option>
                    @foreach ($doctors as $doctor)
                      <option value="{{ $doctor->id }}" @selected((string) old('doctor_id') === (string) $doctor->id)>{{ $doctor->name }} — {{ $doctor->qualification }}</option>
                    @endforeach
                  </select>
                </div>
              @endif
              <div class="appointment-field wide">
                <label for="reason">Visit reason</label>
                <input id="reason" name="reason" value="{{ old('reason') }}" maxlength="250" placeholder="Consultation, follow-up, etc." required>
              </div>
              <div class="appointment-field wide">
                <label for="notes">Notes <span style="font-weight:400">(optional)</span></label>
                <textarea id="notes" name="notes" maxlength="3000" placeholder="Add internal notes for your team">{{ old('notes') }}</textarea>
              </div>
            </div>
            <p class="appointment-help">Scheduled appointments for the same doctor cannot overlap. Patient information is stored in this private workspace.</p>
            <button class="appointment-primary" type="submit"><i class="fas fa-calendar-plus"></i> Save appointment</button>
          </form>
        </section>

        <section class="panel" aria-labelledby="appointment-list-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="appointment-list-title">Appointment register</h2>
              <p class="panel-caption">{{ $appointments->total() }} {{ \Illuminate\Support\Str::plural('appointment', $appointments->total()) }} in this view</p>
            </div>
          </div>
          <form class="appointment-toolbar" method="GET" action="{{ route('user.appointments.index') }}">
            <label class="sr-only" for="appointment-search">Search appointments</label>
            <input id="appointment-search" type="search" name="q" value="{{ $filters['q'] }}" maxlength="100" placeholder="Find patient, contact, reason">
            <label class="sr-only" for="appointment-status">Filter by status</label>
            <select id="appointment-status" name="status">
              <option value="all" @selected($filters['status'] === 'all')>All statuses</option>
              @foreach (['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'] as $status)
                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
              @endforeach
            </select>
            <label class="sr-only" for="appointment-date-from">From date</label>
            <input id="appointment-date-from" type="date" name="date_from" value="{{ $filters['date_from'] }}">
            <label class="sr-only" for="appointment-date-to">To date</label>
            <input id="appointment-date-to" type="date" name="date_to" value="{{ $filters['date_to'] }}">
            <button class="appointment-filter-button" type="submit"><i class="fas fa-filter"></i> Filter</button>
            @if ($filters['q'] !== '' || $filters['status'] !== 'all' || $filters['date_from'] !== '' || $filters['date_to'] !== '')
              <a class="appointment-reset" href="{{ route('user.appointments.index') }}">Clear</a>
            @endif
          </form>

          <div class="appointment-list">
            @forelse ($appointments as $appointment)
              <article class="appointment-card">
                <div class="appointment-card-top">
                  <div class="appointment-patient">
                    <span class="appointment-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($appointment->patient_name, 0, 1)) }}</span>
                    <div class="appointment-patient-copy">
                      <strong>{{ $appointment->patient_name }}</strong>
                      <a href="tel:{{ $appointment->phone }}">{{ $appointment->phone }}</a>
                    </div>
                  </div>
                  <span class="appointment-badge {{ $appointment->status }}">{{ \Illuminate\Support\Str::headline($appointment->status) }}</span>
                </div>
                <div class="appointment-meta">
                  <span><i class="far fa-calendar"></i>{{ $appointment->appointment_at->format('D, M j, Y') }}</span>
                  <span><i class="far fa-clock"></i>{{ $appointment->appointment_at->format('g:i A') }} · {{ $appointment->duration_minutes }} min</span>
                  @if ($appointment->doctor)
                    <span><i class="fas fa-user-md"></i>{{ $appointment->doctor->name }}</span>
                  @endif
                  @if ($appointment->email)
                    <span><i class="far fa-envelope"></i>{{ $appointment->email }}</span>
                  @endif
                </div>
                <p class="appointment-reason"><strong>Reason:</strong> {{ $appointment->reason }}</p>
                @if ($appointment->notes)
                  <p class="appointment-note">{{ $appointment->notes }}</p>
                @endif
                <details class="appointment-edit">
                  <summary><i class="fas fa-pen"></i> Edit appointment</summary>
                  <form class="appointment-form" method="POST" action="{{ route('user.appointments.update', $appointment) }}">
                    @csrf
                    @method('PUT')
                    <div class="appointment-fields">
                      <div class="appointment-field wide">
                        <label for="edit-patient-{{ $appointment->id }}">Patient name</label>
                        <input id="edit-patient-{{ $appointment->id }}" name="patient_name" value="{{ $appointment->patient_name }}" maxlength="120" required>
                      </div>
                      <div class="appointment-field">
                        <label for="edit-phone-{{ $appointment->id }}">Phone number</label>
                        <input id="edit-phone-{{ $appointment->id }}" name="phone" type="tel" value="{{ $appointment->phone }}" maxlength="30" required>
                      </div>
                      <div class="appointment-field">
                        <label for="edit-email-{{ $appointment->id }}">Email (optional)</label>
                        <input id="edit-email-{{ $appointment->id }}" name="email" type="email" value="{{ $appointment->email }}" maxlength="191">
                      </div>
                      <div class="appointment-field">
                        <label for="edit-datetime-{{ $appointment->id }}">Date and time</label>
                        @if ($appointment->appointment_at->isPast())
                          <input type="hidden" name="appointment_at" value="{{ $appointment->appointment_at->format('Y-m-d\TH:i') }}">
                          <input id="edit-datetime-{{ $appointment->id }}" type="text" value="{{ $appointment->appointment_at->format('M j, Y · g:i A') }}" disabled>
                          <p class="appointment-help">Past appointment times cannot be changed.</p>
                        @else
                          <input id="edit-datetime-{{ $appointment->id }}" name="appointment_at" type="datetime-local" value="{{ $appointment->appointment_at->format('Y-m-d\TH:i') }}" min="{{ now()->addMinute()->startOfMinute()->format('Y-m-d\TH:i') }}" required>
                        @endif
                      </div>
                      <div class="appointment-field">
                        <label for="edit-duration-{{ $appointment->id }}">Duration</label>
                        <select id="edit-duration-{{ $appointment->id }}" name="duration_minutes" required>
                          @foreach ([15, 20, 30, 45, 60, 90, 120, 180, 240] as $duration)
                            <option value="{{ $duration }}" @selected($appointment->duration_minutes === $duration)>{{ $duration }} minutes</option>
                          @endforeach
                        </select>
                      </div>
                      @if ($user->role === 1)
                        <div class="appointment-field wide">
                          <label for="edit-doctor-{{ $appointment->id }}">Doctor</label>
                          <select id="edit-doctor-{{ $appointment->id }}" name="doctor_id">
                            <option value="">Not assigned to a doctor</option>
                            @foreach ($doctors as $doctor)
                              <option value="{{ $doctor->id }}" @selected($appointment->doctor_id === $doctor->id)>{{ $doctor->name }} — {{ $doctor->qualification }}</option>
                            @endforeach
                          </select>
                        </div>
                      @endif
                      <div class="appointment-field">
                        <label for="edit-status-{{ $appointment->id }}">Status</label>
                        <select id="edit-status-{{ $appointment->id }}" name="status" required>
                          @foreach (['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'] as $status)
                            <option value="{{ $status }}" @selected($appointment->status === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="appointment-field wide">
                        <label for="edit-reason-{{ $appointment->id }}">Visit reason</label>
                        <input id="edit-reason-{{ $appointment->id }}" name="reason" value="{{ $appointment->reason }}" maxlength="250" required>
                      </div>
                      <div class="appointment-field wide">
                        <label for="edit-notes-{{ $appointment->id }}">Notes</label>
                        <textarea id="edit-notes-{{ $appointment->id }}" name="notes" maxlength="3000">{{ $appointment->notes }}</textarea>
                      </div>
                    </div>
                    <button class="appointment-primary" type="submit"><i class="fas fa-save"></i> Save changes</button>
                  </form>
                </details>
                <div class="appointment-actions">
                  <form method="POST" action="{{ route('user.appointments.destroy', $appointment) }}" onsubmit="return confirm('Delete this appointment? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button class="appointment-delete" type="submit"><i class="far fa-trash-alt"></i> Delete</button>
                  </form>
                </div>
              </article>
            @empty
              <div class="appointment-empty">
                <i class="far fa-calendar-plus"></i>
                <strong>No appointments found</strong>
                <p style="margin:7px 0 0">Schedule your first patient visit or change the search filters.</p>
              </div>
            @endforelse
          </div>
          @if ($appointments->hasPages())
            <div class="appointment-pagination">{{ $appointments->links() }}</div>
          @endif
        </section>
      </div>
      <p class="footer-note"><i class="fas fa-leaf"></i> Thoughtful scheduling, one visit at a time.</p>
    </div>
  </div>
</main>
@endsection
