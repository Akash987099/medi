@extends('layout.app')

@section('title', 'Hospitals')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Users</strong><span> / </span>Hospitals</div>
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
          <p class="eyebrow">Healthcare partners</p>
          <h1>Hospitals</h1>
          <p class="page-subtitle">Register hospitals and manage their contact details and account access.</p>
        </div>
        <button class="category-add-button" type="button" data-open-hospital-modal>
          <i class="fas fa-plus"></i> Register hospital
        </button>
      </div>

      @if (session('status'))
        <div class="category-alert" role="status">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="category-alert error" role="alert">Please review the highlighted fields and try again.</div>
      @endif

      <section class="panel" aria-label="Hospital list">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title">Registered hospitals</h2>
            <p class="panel-caption">{{ $hospitals->total() }} {{ $hospitals->total() === 1 ? 'hospital' : 'hospitals' }} in your network</p>
          </div>
        </div>
        <div class="category-table-wrap">
          <table class="category-table">
            <thead>
              <tr>
                <th scope="col">Hospital</th>
                <th scope="col">Contact</th>
                <th scope="col">Website</th>
                <th scope="col">Status</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($hospitals as $hospital)
                <tr>
                  <td>
                    <div class="doctor-name-cell">
                      <span class="doctor-avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($hospital->name, 0, 1)) }}</span>
                      <span class="doctor-name-copy"><strong>{{ $hospital->name }}</strong><span>{{ $hospital->email }}</span></span>
                    </div>
                  </td>
                  <td>{{ $hospital->phone }}</td>
                  <td>
                    @if ($hospital->website_url)
                      <a class="doctor-website" href="{{ $hospital->website_url }}" target="_blank" rel="noopener noreferrer" aria-label="Visit {{ $hospital->name }}'s website">
                        <i class="fas fa-external-link-alt"></i> Visit
                      </a>
                    @else
                      <span class="category-date">—</span>
                    @endif
                  </td>
                  <td><span class="category-status {{ $hospital->is_active ? 'active' : 'inactive' }}">{{ $hospital->is_active ? 'Active' : 'Inactive' }}</span></td>
                  <td>
                    <div class="category-actions doctor-actions">
                      <button
                        class="category-icon-button"
                        type="button"
                        title="Edit hospital"
                        aria-label="Edit {{ $hospital->name }}"
                        data-edit-hospital
                        data-hospital-id="{{ $hospital->id }}"
                        data-name="{{ $hospital->name }}"
                        data-email="{{ $hospital->email }}"
                        data-phone="{{ $hospital->phone }}"
                        data-website="{{ $hospital->website_url }}"
                        data-address="{{ $hospital->address }}"
                        data-bio="{{ $hospital->bio }}"
                      ><i class="far fa-edit"></i></button>
                      <form action="{{ route('hospital.status', $hospital) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button
                          class="category-icon-button {{ $hospital->is_active ? 'deactivate' : 'activate' }}"
                          type="submit"
                          title="{{ $hospital->is_active ? 'Deactivate hospital' : 'Activate hospital' }}"
                          aria-label="{{ $hospital->is_active ? 'Deactivate' : 'Activate' }} {{ $hospital->name }}"
                        ><i class="fas {{ $hospital->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                      </form>
                      @if ($hospital->user && $hospital->loginCredential && $hospital->loginCredential->user_id === $hospital->user_id)
                        <button class="doctor-login-button" type="button" data-login-access data-entity-label="hospital" data-login-mode="view" data-endpoint="{{ route('hospital.login-access.view', $hospital) }}" title="View existing login credentials">
                          <i class="fas fa-eye"></i> View login
                        </button>
                      @elseif ($hospital->user)
                        <button class="doctor-login-button" type="button" data-login-access data-entity-label="hospital" data-login-mode="reset" data-endpoint="{{ route('hospital.login-access.reset', $hospital) }}" data-view-endpoint="{{ route('hospital.login-access.view', $hospital) }}" title="Create a new password because the old password was not saved">
                          <i class="fas fa-key"></i> Reset login
                        </button>
                      @else
                        <button class="doctor-login-button" type="button" data-login-access data-entity-label="hospital" data-login-mode="create" data-endpoint="{{ route('hospital.login-access', $hospital) }}" data-view-endpoint="{{ route('hospital.login-access.view', $hospital) }}" title="Create hospital login access">
                          <i class="fas fa-key"></i> Login access
                        </button>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td class="category-empty" colspan="5">
                    <i class="fas fa-hospital"></i>
                    No hospitals registered yet. Register your first hospital to get started.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($hospitals->hasPages())
          <div class="category-pagination">{{ $hospitals->links('vendor.pagination.compact') }}</div>
        @endif
      </section>

      <p class="footer-note"><i class="fas fa-leaf"></i> Better care through trusted partnerships.</p>
    </div>
  </div>
</main>

<div class="category-modal" id="hospital-modal" @if (!$errors->any()) hidden @endif>
  <section class="category-dialog doctor-dialog" role="dialog" aria-modal="true" aria-labelledby="hospital-modal-title">
    <div class="category-dialog-header">
      <div>
        <h2 id="hospital-modal-title">{{ old('hospital_id') ? 'Edit hospital' : 'Register a hospital' }}</h2>
        <p>Add the hospital's contact and facility details.</p>
      </div>
      <button class="category-modal-close" type="button" aria-label="Close dialog" data-close-hospital-modal><i class="fas fa-times"></i></button>
    </div>

    <form
      class="category-form"
      id="hospital-form"
      data-login-csrf
      action="{{ old('hospital_id') ? route('hospital.update', old('hospital_id')) : route('hospital.store') }}"
      method="POST"
      data-edit-mode="{{ old('hospital_id') ? 'true' : 'false' }}"
      data-create-action="{{ route('hospital.store') }}"
      data-update-base="{{ url('/users/hospitals') }}"
    >
      @csrf
      <input type="hidden" name="_method" value="PUT" @disabled(!old('hospital_id'))>
      <input type="hidden" name="hospital_id" value="{{ old('hospital_id') }}">
      <div class="doctor-form-grid">
        <div class="category-field">
          <label for="hospital-name">Hospital name</label>
          <input class="category-input" id="hospital-name" name="name" type="text" value="{{ old('name') }}" maxlength="120" placeholder="Riverside Medical Center" required>
          @error('name')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field">
          <label for="hospital-email">Email address</label>
          <input class="category-input" id="hospital-email" name="email" type="email" value="{{ old('email') }}" maxlength="191" placeholder="contact@hospital.example" required>
          @error('email')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field">
          <label for="hospital-phone">Phone number</label>
          <input class="category-input" id="hospital-phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="30" placeholder="+1 555 0123" required>
          @error('phone')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field doctor-field-wide">
          <label for="hospital-website">Website URL <span class="doctor-optional">(optional)</span></label>
          <input class="category-input" id="hospital-website" name="website_url" type="url" value="{{ old('website_url') }}" maxlength="2048" placeholder="https://example.com">
          @error('website_url')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field doctor-field-wide">
          <label for="hospital-address">Hospital address <span class="doctor-optional">(optional)</span></label>
          <textarea class="category-textarea doctor-short-textarea" id="hospital-address" name="address" maxlength="2000" placeholder="Hospital address">{{ old('address') }}</textarea>
          @error('address')<p class="category-error">{{ $message }}</p>@enderror
        </div>
        <div class="category-field doctor-field-wide">
          <label for="hospital-bio">About hospital <span class="doctor-optional">(optional)</span></label>
          <textarea class="category-textarea doctor-short-textarea" id="hospital-bio" name="bio" maxlength="5000" placeholder="Facility profile or additional details">{{ old('bio') }}</textarea>
          @error('bio')<p class="category-error">{{ $message }}</p>@enderror
        </div>
      </div>
      <div class="category-form-actions">
        <button class="category-cancel" type="button" data-close-hospital-modal>Cancel</button>
        <button class="category-save" type="submit">{{ old('hospital_id') ? 'Save changes' : 'Register hospital' }}</button>
      </div>
    </form>
  </section>
</div>

<div class="category-modal" id="doctor-credentials-modal" hidden>
  <section class="category-dialog doctor-credentials-dialog" role="dialog" aria-modal="true" aria-labelledby="doctor-credentials-title">
    <div class="category-dialog-header">
      <div>
        <h2 id="doctor-credentials-title">Hospital login access</h2>
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
