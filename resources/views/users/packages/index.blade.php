@extends('users.layout.app')

@section('title', 'Packages')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Packages</div>
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
      <section class="page-heading" aria-labelledby="packages-title">
        <div>
          <p class="eyebrow">Combine your services</p>
          <h1 id="packages-title">Packages</h1>
          <p class="page-subtitle">Create service packages using active services added to your account.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="user-package-alert" role="status">{{ session('status') }}</div>
      @endif

      <section class="user-package-panel panel" aria-labelledby="create-package-title">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title" id="create-package-title">Create a package</h2>
            <p class="panel-caption">Choose one or more of your active services to include.</p>
          </div>
        </div>
        @if ($services->isEmpty())
          <p class="user-package-empty-note">Add and activate your own services in Services before creating a package.</p>
        @else
          <form class="user-package-form" method="POST" action="{{ route('user.packages.store') }}">
            @csrf
            <div class="user-package-field">
              <label for="package-name">Package name</label>
              <input id="package-name" type="text" name="name" value="{{ old('name') }}" maxlength="120" placeholder="e.g. Complete wellness package" required>
              @error('name')<span class="user-package-error">{{ $message }}</span>@enderror
            </div>
            <div class="user-package-field">
              <label for="package-price">Price (Rs.)</label>
              <input id="package-price" type="number" name="price" value="{{ old('price') }}" min="0" max="99999999.99" step="0.01" required>
              @error('price')<span class="user-package-error">{{ $message }}</span>@enderror
            </div>
            <div class="user-package-field user-package-wide">
              <label for="package-description">Description <span>(optional)</span></label>
              <textarea id="package-description" name="description" rows="3" maxlength="5000" placeholder="What does this package include?">{{ old('description') }}</textarea>
              @error('description')<span class="user-package-error">{{ $message }}</span>@enderror
            </div>
            <fieldset class="user-package-services user-package-wide">
              <legend>Included services</legend>
              <p class="user-package-service-hint">Select one or more active services from your Services menu.</p>
              @error('service_ids')<span class="user-package-error">{{ $message }}</span>@enderror
              <div class="user-package-service-list">
                @foreach ($services as $service)
                  <label class="user-package-service-option">
                    <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, (array) old('service_ids', [])))>
                    <span>{{ $service->title ?: $service->service?->name }}</span>
                  </label>
                @endforeach
              </div>
              @error('service_ids.*')<span class="user-package-error">{{ $message }}</span>@enderror
            </fieldset>
            <button class="user-package-submit" type="submit"><i class="fas fa-plus"></i> Create package</button>
          </form>
        @endif
      </section>

      <section class="user-package-panel panel" aria-labelledby="your-packages-title">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title" id="your-packages-title">Your packages</h2>
            <p class="panel-caption">Packages and selected services are saved to your account.</p>
          </div>
        </div>
        @if ($packages->isEmpty())
          <p class="user-package-empty-note">You have not created any packages yet.</p>
        @else
          <div class="user-package-list">
            @foreach ($packages as $package)
              <article class="user-package-card">
                <div class="user-package-card-heading">
                  <div>
                    <span class="user-package-status {{ $package->is_active ? 'active' : 'inactive' }}">{{ $package->is_active ? 'Active' : 'Inactive' }}</span>
                    <h3>{{ $package->name }}</h3>
                    <p class="user-package-price">Rs. {{ number_format((float) $package->price, 2) }}</p>
                  </div>
                  <div class="user-package-actions">
                    <form method="POST" action="{{ route('user.packages.status', $package->id) }}">
                      @csrf
                      @method('PATCH')
                      <button class="user-package-secondary" type="submit">{{ $package->is_active ? 'Deactivate' : 'Activate' }}</button>
                    </form>
                    <form method="POST" action="{{ route('user.packages.destroy', $package->id) }}" onsubmit="return confirm('Delete this package?')">
                      @csrf
                      @method('DELETE')
                      <button class="user-package-delete" type="submit" aria-label="Delete {{ $package->name }}"><i class="fas fa-trash-alt"></i></button>
                    </form>
                  </div>
                </div>
                @if ($package->description)
                  <p class="user-package-description">{{ $package->description }}</p>
                @endif
                <div class="user-package-included">
                  <strong>Included services</strong>
                  @if ($package->services->isNotEmpty())
                    <ul>
                      @foreach ($package->services as $service)
                        <li>{{ $service->title }}</li>
                      @endforeach
                    </ul>
                  @else
                    <p>No services currently linked. Edit the package to choose active services.</p>
                  @endif
                </div>
                <details class="user-package-edit">
                  <summary>Edit package</summary>
                  <form class="user-package-form" method="POST" action="{{ route('user.packages.update', $package->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="user-package-field">
                      <label for="package-name-{{ $package->id }}">Package name</label>
                      <input id="package-name-{{ $package->id }}" type="text" name="name" value="{{ $package->name }}" maxlength="120" required>
                    </div>
                    <div class="user-package-field">
                      <label for="package-price-{{ $package->id }}">Price (Rs.)</label>
                      <input id="package-price-{{ $package->id }}" type="number" name="price" value="{{ $package->price }}" min="0" max="99999999.99" step="0.01" required>
                    </div>
                    <div class="user-package-field user-package-wide">
                      <label for="package-description-{{ $package->id }}">Description <span>(optional)</span></label>
                      <textarea id="package-description-{{ $package->id }}" name="description" rows="3" maxlength="5000">{{ $package->description }}</textarea>
                    </div>
                    <fieldset class="user-package-services user-package-wide">
                      <legend>Included services</legend>
                      @if ($services->isEmpty())
                        <p class="user-package-empty-note">Add and activate services before editing this package.</p>
                      @else
                        <div class="user-package-service-list">
                          @foreach ($services as $service)
                            <label class="user-package-service-option">
                              <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked($package->services->contains('id', $service->id))>
                              <span>{{ $service->title ?: $service->service?->name }}</span>
                            </label>
                          @endforeach
                        </div>
                      @endif
                    </fieldset>
                    <button class="user-package-submit" type="submit" @disabled($services->isEmpty())>Save changes</button>
                  </form>
                </details>
              </article>
            @endforeach
          </div>
        @endif
      </section>
    </div>
  </div>
</main>

<style>
  .user-package-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .user-package-panel { margin-bottom: 20px; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .user-package-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: end; gap: 13px; padding: 0 20px 20px; }
  .user-package-field { min-width: 0; display: grid; gap: 7px; }
  .user-package-wide { grid-column: 1 / -1; }
  .user-package-field label, .user-package-services legend { color: #59665b; font-size: 12px; font-weight: 700; }
  .user-package-field label span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .user-package-field input, .user-package-field textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .user-package-field input:focus, .user-package-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .user-package-field textarea { resize: vertical; line-height: 1.6; }
  .user-package-services { min-width: 0; margin: 0; padding: 12px; border: 1px solid var(--line); border-radius: 9px; }
  .user-package-service-hint { margin: 7px 0 0; color: var(--muted); font-size: 11px; }
  .user-package-service-list { display: flex; flex-wrap: wrap; gap: 9px; margin-top: 10px; }
  .user-package-service-option { display: inline-flex; align-items: center; gap: 7px; padding: 8px 10px; color: var(--ink); background: #f7f8f4; border: 1px solid var(--line); border-radius: 7px; font-size: 12px; cursor: pointer; }
  .user-package-service-option input { accent-color: var(--green-dark); }
  .user-package-error { color: #a34d40; font-size: 11px; }
  .user-package-submit { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .user-package-submit:hover { background: var(--green); }
  .user-package-submit:disabled { opacity: .55; cursor: not-allowed; }
  .user-package-empty-note { margin: 0; padding: 0 20px 20px; color: var(--muted); font-size: 12px; }
  .user-package-list { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; padding: 0 18px 18px; }
  .user-package-card { min-width: 0; padding: 16px; background: #fff; border: 1px solid var(--line); border-radius: 11px; }
  .user-package-card-heading { display: flex; justify-content: space-between; gap: 12px; }
  .user-package-card h3 { margin: 8px 0 4px; color: var(--ink); font-size: 17px; }
  .user-package-price { margin: 0; color: var(--green-dark); font-size: 13px; font-weight: 700; }
  .user-package-status { display: inline-block; padding: 4px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
  .user-package-status.active { color: #2f7048; background: #e8f2e8; }
  .user-package-status.inactive { color: #91614a; background: #f5ece5; }
  .user-package-actions { display: flex; align-items: flex-start; gap: 7px; }
  .user-package-secondary, .user-package-delete { min-height: 32px; padding: 0 9px; color: #526250; background: #f4f6f1; border: 1px solid var(--line); border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .user-package-delete { color: #a34d40; }
  .user-package-description, .user-package-included { color: #687368; font-size: 12px; line-height: 1.6; }
  .user-package-included { margin: 13px 0; padding: 10px; background: #f7f8f4; border-radius: 8px; }
  .user-package-included strong { color: var(--ink); }
  .user-package-included ul { margin: 7px 0 0; padding-left: 18px; }
  .user-package-included p { margin: 6px 0 0; }
  .user-package-edit { border-top: 1px solid var(--line); padding-top: 11px; }
  .user-package-edit summary { color: var(--green-dark); cursor: pointer; font-size: 11px; font-weight: 700; }
  .user-package-edit .user-package-form { margin-top: 13px; padding: 0; }
  @media (max-width: 800px) { .user-package-list { grid-template-columns: 1fr; } }
  @media (max-width: 560px) { .user-package-form { grid-template-columns: 1fr; padding-right: 14px; padding-left: 14px; } .user-package-wide { grid-column: auto; } .user-package-card-heading { flex-direction: column; } .user-package-actions { justify-content: flex-start; } }
</style>
@endsection
