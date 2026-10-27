@extends('layout.app')

@section('title', 'Doctors')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Users</strong><span> / </span>Doctors</div>
    </div>
    <div class="topbar-actions">
      <button class="icon-button" type="button" aria-label="Notifications">
        <i class="far fa-bell"></i><span class="notification-dot"></span>
      </button>
      <div class="admin-profile">
        <div class="avatar" aria-hidden="true">AD</div>
        <div class="profile-copy">
          <strong>Administrator</strong>
          <span>Clinic manager</span>
        </div>
      </div>
    </div>
  </header>

  <div class="content-scroll">
    <div class="content-wrap">
      <div class="category-toolbar">
        <div>
          <p class="eyebrow">Your care team</p>
          <h1>Doctors</h1>
          <p class="page-subtitle">Register doctors and keep their clinic details together.</p>
        </div>
        <button class="category-add-button" type="button" data-open-doctor-modal>
          <i class="fas fa-plus"></i> Register doctor
        </button>
      </div>

      @if (session('status'))
        <div class="category-alert" role="status">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="category-alert error" role="alert">Please review the highlighted fields and try again.</div>
      @endif

      <section class="panel" aria-label="Doctor list">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title">Registered doctors</h2>
            <p class="panel-caption">{{ $doctors->total() }} {{ $doctors->total() === 1 ? 'doctor' : 'doctors' }} in your clinic</p>
          </div>
        </div>
        <div class="category-table-wrap">
          <table class="category-table">
            <thead>
              <tr>
                <th scope="col">Doctor</th>
                <th scope="col">Category</th>
                <th scope="col">Hospital</th>
                <th scope="col">Contact</th>
                <th scope="col">Qualification</th>
                <th scope="col">Experience</th>
                <th scope="col">Website</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($doctors as $doctor)
                <tr>
                  <td>
                    <div class="doctor-name-cell">
                      <span class="doctor-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($doctor->name, 0, 1)) }}</span>
                      <span class="doctor-name-copy"><strong>{{ $doctor->name }}</strong><span>{{ $doctor->email }}</span></span>
                    </div>
                  </td>
                  <td>{{ $doctor->category->name }}</td>
                  <td>{{ $doctor->hospital?->name ?? '—' }}</td>
                  <td>{{ $doctor->phone }}</td>
                  <td>{{ $doctor->qualification }}</td>
                  <td>{{ $doctor->experience_years }} {{ $doctor->experience_years === 1 ? 'year' : 'years' }}</td>
                  <td>
                    @if ($doctor->website_url)
                      <a class="doctor-website" href="{{ $doctor->website_url }}" target="_blank" rel="noopener noreferrer" aria-label="Visit {{ $doctor->name }}'s website">
                        <i class="fas fa-external-link-alt"></i> Visit
                      </a>
                    @else
                      <span class="category-date">—</span>
                    @endif
                  </td>
                  <td><span class="category-status {{ $doctor->is_active ? 'active' : 'inactive' }}">{{ $doctor->is_active ? 'Active' : 'Inactive' }}</span></td>
                  <td>
                    <div class="category-actions doctor-actions">
                      <button
                        class="category-icon-button"
                        type="button"
                        title="Edit doctor"
                        aria-label="Edit {{ $doctor->name }}"
                        data-edit-doctor
                        data-doctor-id="{{ $doctor->id }}"
                        data-category-id="{{ $doctor->category_id }}"
                        data-hospital-id="{{ $doctor->hospital_id }}"
                        data-name="{{ $doctor->name }}"
                        data-email="{{ $doctor->email }}"
                        data-phone="{{ $doctor->phone }}"
                        data-website="{{ $doctor->website_url }}"
                        data-qualification="{{ $doctor->qualification }}"
                        data-experience="{{ $doctor->experience_years }}"
                        data-address="{{ $doctor->address }}"
                        data-bio="{{ $doctor->bio }}"
                      ><i class="far fa-edit"></i></button>
                      <form action="{{ route('doctor.status', $doctor) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button
                          class="category-icon-button {{ $doctor->is_active ? 'deactivate' : 'activate' }}"
                          type="submit"
                          title="{{ $doctor->is_active ? 'Deactivate doctor' : 'Activate doctor' }}"
                          aria-label="{{ $doctor->is_active ? 'Deactivate' : 'Activate' }} {{ $doctor->name }}"
                        ><i class="fas {{ $doctor->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                      </form>
                      @if ($doctor->user && $doctor->loginCredential && $doctor->loginCredential->user_id === $doctor->user_id)
                        <button
                          class="doctor-login-button"
                          type="button"
                          data-doctor-login
                          data-login-access
                          data-entity-label="doctor"
                          data-login-mode="view"
                          data-endpoint="{{ route('doctor.login-access.view', $doctor) }}"
                          title="View the existing login credentials"
                        ><i class="fas fa-eye"></i> View login</button>
                      @elseif ($doctor->user)
                        <button
                          class="doctor-login-button"
                          type="button"
                          data-doctor-login
                          data-login-access
                          data-entity-label="doctor"
                          data-login-mode="reset"
                          data-endpoint="{{ route('doctor.login-access.reset', $doctor) }}"
                          data-view-endpoint="{{ route('doctor.login-access.view', $doctor) }}"
                          title="Create a new password because the old password was not saved"
                        ><i class="fas fa-key"></i> Reset login</button>
                      @else
                        <button
                          class="doctor-login-button"
                          type="button"
                          data-doctor-login
                          data-login-access
                          data-entity-label="doctor"
                          data-login-mode="create"
                          data-endpoint="{{ route('doctor.login-access', $doctor) }}"
                          data-view-endpoint="{{ route('doctor.login-access.view', $doctor) }}"
                          data-doctor-name="{{ $doctor->name }}"
                        ><i class="fas fa-key"></i> Login access</button>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td class="category-empty" colspan="9">
                    <i class="fas fa-user-md"></i>
                    No doctors registered yet. Register your first doctor to get started.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($doctors->hasPages())
          <div class="category-pagination">{{ $doctors->links('vendor.pagination.compact') }}</div>
        @endif
      </section>

      <p class="footer-note"><i class="fas fa-leaf"></i> Thoughtful care, one visit at a time.</p>
    </div>
  </div>
</main>

<div class="category-modal" id="doctor-modal" @if (!$errors->any()) hidden @endif>
  <section class="category-dialog doctor-dialog" role="dialog" aria-modal="true" aria-labelledby="doctor-modal-title">
    <div class="category-dialog-header">
      <div>
        <h2 id="doctor-modal-title">{{ old('doctor_id') ? 'Edit doctor' : 'Register a doctor' }}</h2>
        <p>Add the doctor's contact, specialty, and professional details.</p>
      </div>
      <button class="category-modal-close" type="button" aria-label="Close dialog" data-close-doctor-modal><i class="fas fa-times"></i></button>
    </div>

    <form
      class="category-form"
      id="doctor-form"
      data-login-csrf
      action="{{ old('doctor_id') ? route('doctor.update', old('doctor_id')) : route('doctor.store') }}"
      method="POST"
      data-edit-mode="{{ old('doctor_id') ? 'true' : 'false' }}"
      data-create-action="{{ route('doctor.store') }}"
      data-update-base="{{ url('/users/doctors') }}"
    >
      @csrf
      <input type="hidden" name="_method" value="PUT" @disabled(!old('doctor_id'))>
      <input type="hidden" name="doctor_id" value="{{ old('doctor_id') }}">
      <div class="doctor-form-grid">
        <div class="category-field">
          <label for="doctor-name">Full name</label>
          <input class="category-input" id="doctor-name" name="name" type="text" value="{{ old('name') }}" maxlength="120" placeholder="Dr. Alex Morgan" required>
          @error('name')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field">
          <label for="doctor-category">Category / specialty</label>
          <select class="category-input" id="doctor-category" name="category_id" required>
            <option value="">Select a category</option>
            @foreach ($categories as $category)
              <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
          </select>
          @if ($categories->isEmpty())
            <p class="category-help">Add and activate a category before registering a doctor.</p>
          @endif
          @error('category_id')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field">
          <label for="doctor-hospital">Hospital <span class="doctor-optional">(optional)</span></label>
          <select class="category-input" id="doctor-hospital" name="hospital_id">
            <option value="">No hospital selected</option>
            @foreach ($hospitals as $hospital)
              <option value="{{ $hospital->id }}" @selected((string) old('hospital_id') === (string) $hospital->id)>
                {{ $hospital->name }}{{ $hospital->is_active ? '' : ' (Inactive)' }}
              </option>
            @endforeach
          </select>
          @error('hospital_id')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field">
          <label for="doctor-email">Email address</label>
          <input class="category-input" id="doctor-email" name="email" type="email" value="{{ old('email') }}" maxlength="191" placeholder="doctor@example.com" required>
          @error('email')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field">
          <label for="doctor-phone">Phone number</label>
          <input class="category-input" id="doctor-phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="30" placeholder="+1 555 0123" required>
          @error('phone')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field">
          <label for="doctor-qualification">Qualification</label>
          <input class="category-input" id="doctor-qualification" name="qualification" type="text" value="{{ old('qualification') }}" maxlength="160" placeholder="MD, MBBS, etc." required>
          @error('qualification')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field">
          <label for="doctor-experience">Experience (years)</label>
          <input class="category-input" id="doctor-experience" name="experience_years" type="number" value="{{ old('experience_years', 0) }}" min="0" max="80" required>
          @error('experience_years')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field doctor-field-wide">
          <label for="doctor-website">Website URL <span class="doctor-optional">(optional)</span></label>
          <input class="category-input" id="doctor-website" name="website_url" type="url" value="{{ old('website_url') }}" maxlength="2048" placeholder="https://example.com">
          @error('website_url')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field doctor-field-wide">
          <label for="doctor-address">Clinic address <span class="doctor-optional">(optional)</span></label>
          <textarea class="category-textarea doctor-short-textarea" id="doctor-address" name="address" maxlength="2000" placeholder="Clinic address">{{ old('address') }}</textarea>
          @error('address')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field doctor-field-wide">
          <label for="doctor-bio">About doctor <span class="doctor-optional">(optional)</span></label>
          <textarea class="category-textarea doctor-short-textarea" id="doctor-bio" name="bio" maxlength="5000" placeholder="Professional profile or additional details">{{ old('bio') }}</textarea>
          @error('bio')<p class="category-error">{{ $message }}</p>@enderror
        </div>
      </div>
      <div class="category-form-actions">
        <button class="category-cancel" type="button" data-close-doctor-modal>Cancel</button>
        <button class="category-save" type="submit" @disabled($categories->isEmpty())>{{ old('doctor_id') ? 'Save changes' : 'Register doctor' }}</button>
      </div>
    </form>
  </section>
</div>

<div class="category-modal" id="doctor-credentials-modal" hidden>
  <section class="category-dialog doctor-credentials-dialog" role="dialog" aria-modal="true" aria-labelledby="doctor-credentials-title">
    <div class="category-dialog-header">
      <div>
        <h2 id="doctor-credentials-title">Doctor login access</h2>
        <p id="doctor-credentials-message">Existing login credentials.</p>
      </div>
      <button class="category-modal-close" type="button" aria-label="Close credentials" data-close-doctor-credentials><i class="fas fa-times"></i></button>
    </div>
    <div class="doctor-credentials-content">
      <div class="category-alert error" id="doctor-credentials-error" role="alert" hidden></div>
      <div class="doctor-credential-row">
        <span>Username</span>
        <strong id="doctor-generated-username"></strong>
        <button class="category-icon-button" type="button" data-copy-credential="username" title="Copy username" aria-label="Copy username"><i class="far fa-copy"></i></button>
      </div>
      <div class="doctor-credential-row">
        <span>Password</span>
        <strong id="doctor-generated-password"></strong>
        <button class="category-icon-button" type="button" data-copy-credential="password" title="Copy password" aria-label="Copy password"><i class="far fa-copy"></i></button>
      </div>
        <p class="category-help" id="doctor-credentials-note">Passwords are hashed for sign-in and stored encrypted for secure administrator viewing.</p>
      <div class="category-form-actions">
        <button class="category-save" type="button" data-close-doctor-credentials>Done</button>
      </div>
    </div>
  </section>
</div>
@endsection
