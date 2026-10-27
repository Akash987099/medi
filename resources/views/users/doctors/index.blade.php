@extends('users.layout.app')

@section('title', 'Doctors')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Doctors</div>
    </div>
    <div class="topbar-actions">
      <div class="admin-profile">
        <div class="avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($user->name, 0, 1)) }}</div>
        <div class="profile-copy">
          <strong>{{ $user->name }}</strong>
          <span>Hospital account</span>
        </div>
      </div>
    </div>
  </header>

  <div class="content-scroll">
    <div class="content-wrap">
      <section class="page-heading" aria-labelledby="hospital-doctors-title">
        <div>
          <p class="eyebrow">{{ $hospital->name }}</p>
          <h1 id="hospital-doctors-title">Doctors</h1>
          <p class="page-subtitle">Manage the doctors assigned to your hospital.</p>
        </div>
        <a class="hospital-doctors-add-button" href="#add-hospital-doctor"><i class="fas fa-plus" aria-hidden="true"></i> Add doctor</a>
      </section>

      @if (session('status'))
        <div class="hospital-doctors-notice" role="status">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="hospital-doctors-notice error" role="alert">Please correct the highlighted fields and submit the form again.</div>
      @endif

      <section class="panel hospital-doctors-panel" aria-labelledby="hospital-doctors-list-title">
        <div class="panel-heading hospital-doctors-heading">
          <div>
            <h2 class="panel-title" id="hospital-doctors-list-title">Hospital doctors</h2>
            <p class="panel-caption">{{ $doctors->total() }} {{ \Illuminate\Support\Str::plural('doctor', $doctors->total()) }} assigned to {{ $hospital->name }}</p>
          </div>
          <form class="hospital-doctors-filter" method="GET" action="{{ route('user.doctors.index') }}">
            <label class="hospital-doctors-search" for="hospital-doctors-search">
              <i class="fas fa-search" aria-hidden="true"></i>
              <input id="hospital-doctors-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Search name or specialty">
            </label>
            <label class="hospital-doctors-status-filter" for="hospital-doctors-status">
              <span class="sr-only">Filter by status</span>
              <select id="hospital-doctors-status" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
              </select>
            </label>
            <button class="hospital-doctors-filter-button" type="submit">Find</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
              <a class="hospital-doctors-reset" href="{{ route('user.doctors.index') }}">Clear</a>
            @endif
          </form>
        </div>

        <div class="hospital-doctors-grid">
          @forelse ($doctors as $doctor)
            <article class="hospital-doctor-card">
              <div class="hospital-doctor-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($doctor->name, 0, 1)) }}</div>
              <div class="hospital-doctor-content">
                <div class="hospital-doctor-heading">
                  <div>
                    <h3>{{ $doctor->name }}</h3>
                    <p>{{ $doctor->qualification }}</p>
                  </div>
                  <span class="hospital-doctor-status {{ $doctor->is_active ? 'active' : 'inactive' }}">{{ $doctor->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="hospital-doctor-details">
                  @if ($doctor->category)
                    <span><i class="fas fa-stethoscope" aria-hidden="true"></i> {{ $doctor->category->name }}</span>
                  @endif
                  @if ($doctor->experience_years !== null)
                    <span><i class="far fa-clock" aria-hidden="true"></i> {{ $doctor->experience_years }} {{ \Illuminate\Support\Str::plural('year', $doctor->experience_years) }} experience</span>
                  @endif
                  @if ($doctor->email)
                    <a href="mailto:{{ $doctor->email }}"><i class="far fa-envelope" aria-hidden="true"></i> {{ $doctor->email }}</a>
                  @endif
                  @if ($doctor->phone)
                    <a href="tel:{{ $doctor->phone }}"><i class="fas fa-phone" aria-hidden="true"></i> {{ $doctor->phone }}</a>
                  @endif
                </div>
                @if ($doctor->bio)
                  <p class="hospital-doctor-bio">{{ $doctor->bio }}</p>
                @endif
              </div>
            </article>
          @empty
            <div class="hospital-doctors-empty">
              <span><i class="fas fa-user-md"></i></span>
              <h3>No doctors found</h3>
              <p>No doctors are currently assigned to this hospital, or none match your search.</p>
            </div>
          @endforelse
        </div>
        @if ($doctors->hasPages())
          <div class="hospital-doctors-pagination">{{ $doctors->links('vendor.pagination.compact') }}</div>
        @endif
      </section>

      <section class="panel hospital-doctors-form-panel" id="add-hospital-doctor" aria-labelledby="add-hospital-doctor-title">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title" id="add-hospital-doctor-title">Add a doctor</h2>
            <p class="panel-caption">The doctor will be assigned to {{ $hospital->name }}.</p>
          </div>
        </div>
        <form class="hospital-doctor-form" method="POST" action="{{ route('user.doctors.store') }}">
          @csrf
          <div class="hospital-doctor-form-grid">
            <label>
              <span>Full name</span>
              <input type="text" name="name" value="{{ old('name') }}" maxlength="120" required>
              @error('name')<small>{{ $message }}</small>@enderror
            </label>
            <label>
              <span>Specialty</span>
              <select name="category_id" required>
                <option value="">Select a category</option>
                @foreach ($categories as $category)
                  <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                @endforeach
              </select>
              @if ($categories->isEmpty())<small>Add an active category before adding a doctor.</small>@endif
              @error('category_id')<small>{{ $message }}</small>@enderror
            </label>
            <label>
              <span>Email</span>
              <input type="email" name="email" value="{{ old('email') }}" maxlength="191" required>
              @error('email')<small>{{ $message }}</small>@enderror
            </label>
            <label>
              <span>Phone</span>
              <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="30" required>
              @error('phone')<small>{{ $message }}</small>@enderror
            </label>
            <label>
              <span>Qualification</span>
              <input type="text" name="qualification" value="{{ old('qualification') }}" maxlength="160" required>
              @error('qualification')<small>{{ $message }}</small>@enderror
            </label>
            <label>
              <span>Experience (years)</span>
              <input type="number" name="experience_years" value="{{ old('experience_years', 0) }}" min="0" max="80" required>
              @error('experience_years')<small>{{ $message }}</small>@enderror
            </label>
            <label>
              <span>Website <em>(optional)</em></span>
              <input type="url" name="website_url" value="{{ old('website_url') }}" maxlength="2048">
              @error('website_url')<small>{{ $message }}</small>@enderror
            </label>
            <label class="wide">
              <span>Address <em>(optional)</em></span>
              <textarea name="address" maxlength="2000" rows="2">{{ old('address') }}</textarea>
              @error('address')<small>{{ $message }}</small>@enderror
            </label>
            <label class="wide">
              <span>About doctor <em>(optional)</em></span>
              <textarea name="bio" maxlength="5000" rows="3">{{ old('bio') }}</textarea>
              @error('bio')<small>{{ $message }}</small>@enderror
            </label>
          </div>
          <div class="hospital-doctor-form-actions">
            <button type="submit" @disabled($categories->isEmpty())>Add doctor</button>
          </div>
        </form>
      </section>
    </div>
  </div>
</main>

<style>
  .hospital-doctors-add-button { min-height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 14px; color: #fff; background: var(--green-dark); border-radius: 7px; text-decoration: none; font-size: 11px; font-weight: 700; }
  .hospital-doctors-notice { margin-bottom: 14px; padding: 12px 14px; color: #365844; background: #e8f1e6; border-radius: 8px; font-size: 12px; }
  .hospital-doctors-notice.error { color: #8b3f36; background: #fbe9e6; }
  .hospital-doctors-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .hospital-doctors-form-panel { margin-top: 18px; padding-bottom: 18px; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); scroll-margin-top: 24px; }
  .hospital-doctor-form { padding: 0 18px; }
  .hospital-doctor-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 13px; }
  .hospital-doctor-form-grid label { min-width: 0; display: grid; align-content: start; gap: 6px; color: #59665b; font-size: 11px; font-weight: 650; }
  .hospital-doctor-form-grid label.wide { grid-column: 1 / -1; }
  .hospital-doctor-form-grid label em { color: #849084; font-style: normal; font-weight: 400; }
  .hospital-doctor-form-grid input, .hospital-doctor-form-grid select, .hospital-doctor-form-grid textarea { width: 100%; min-height: 38px; padding: 9px 10px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 11px; }
  .hospital-doctor-form-grid textarea { resize: vertical; }
  .hospital-doctor-form-grid small { color: #a23e35; font-size: 10px; font-weight: 400; }
  .hospital-doctor-form-actions { display: flex; justify-content: flex-end; margin-top: 14px; }
  .hospital-doctor-form-actions button { min-height: 38px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font: inherit; font-size: 11px; font-weight: 700; }
  .hospital-doctor-form-actions button:disabled { opacity: .55; cursor: not-allowed; }
  .hospital-doctors-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
  .hospital-doctors-filter { min-width: min(100%, 470px); display: flex; align-items: center; gap: 8px; }
  .hospital-doctors-search { min-width: 0; min-height: 37px; display: flex; flex: 1; align-items: center; gap: 8px; padding: 0 11px; color: #849084; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; }
  .hospital-doctors-search input { width: 100%; min-width: 0; padding: 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 11px; }
  .hospital-doctors-status-filter select { min-height: 37px; max-width: 125px; padding: 0 8px; color: #59665b; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 10px; }
  .hospital-doctors-filter-button, .hospital-doctors-reset { min-height: 37px; display: inline-flex; align-items: center; justify-content: center; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .hospital-doctors-reset { color: #68756a; background: #f1f3ed; text-decoration: none; }
  .hospital-doctors-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; gap: 12px; padding: 0 18px 18px; }
  .hospital-doctor-card { min-width: 0; display: flex; gap: 12px; padding: 15px; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
  .hospital-doctor-avatar { width: 42px; height: 42px; flex: 0 0 42px; display: grid; place-items: center; color: var(--green-dark); background: var(--sage); border-radius: 50%; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .hospital-doctor-content { min-width: 0; flex: 1; }
  .hospital-doctor-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
  .hospital-doctor-heading h3 { margin: 1px 0 3px; font-size: 12px; overflow-wrap: anywhere; }
  .hospital-doctor-heading p { margin: 0; color: var(--muted); font-size: 10px; line-height: 1.5; }
  .hospital-doctor-status { flex: 0 0 auto; padding: 5px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
  .hospital-doctor-status.active { color: #365844; background: #e8f1e6; }
  .hospital-doctor-status.inactive { color: #8b6557; background: #f5ece7; }
  .hospital-doctor-details { display: grid; gap: 6px; margin-top: 11px; }
  .hospital-doctor-details span, .hospital-doctor-details a { color: #647064; font-size: 10px; line-height: 1.5; overflow-wrap: anywhere; text-decoration: none; }
  .hospital-doctor-details i { width: 14px; color: var(--green); }
  .hospital-doctor-details a:hover { color: var(--green-dark); text-decoration: underline; }
  .hospital-doctor-bio { margin: 10px 0 0; color: #647064; font-size: 10px; line-height: 1.6; white-space: pre-line; overflow-wrap: anywhere; }
  .hospital-doctors-empty { grid-column: 1 / -1; display: grid; justify-items: center; padding: 46px 20px; text-align: center; }
  .hospital-doctors-empty > span { width: 46px; height: 46px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .hospital-doctors-empty h3 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .hospital-doctors-empty p { margin: 0; color: var(--muted); font-size: 12px; }
  .hospital-doctors-pagination { display: flex; justify-content: center; padding: 12px 20px 18px; border-top: 1px solid var(--line); }
  .hospital-doctors-pagination .pagination-links { display: flex; align-items: center; gap: 5px; }
  .hospital-doctors-pagination .pagination-links a, .hospital-doctors-pagination .pagination-links > span { min-width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: #68756a; font-size: 11px; }
  .hospital-doctors-pagination .pagination-links [aria-current=page] { color: #fff; background: var(--green); border-color: var(--green); }
  .hospital-doctors-pagination svg { width: 15px; height: 15px; }
  @media (max-width: 1000px) {   .hospital-doctor-form-grid { grid-template-columns: 1fr; }
  .hospital-doctor-form-grid label.wide { grid-column: auto; }
  .hospital-doctors-heading { flex-direction: column; } .hospital-doctors-filter { width: 100%; } }
  @media (max-width: 700px) { .hospital-doctors-grid { grid-template-columns: 1fr; } }
  @media (max-width: 520px) { .hospital-doctors-filter { flex-wrap: wrap; } .hospital-doctors-search { flex-basis: 100%; } .hospital-doctor-card { padding: 12px; } }
</style>
@endsection
