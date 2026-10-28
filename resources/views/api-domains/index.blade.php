@extends('layout.app')

@section('title', 'API Domains')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>API Domains</div>
    </div>
    <div class="topbar-actions">
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
      <section class="page-heading" aria-labelledby="api-domains-title">
        <div>
          <p class="eyebrow">Connect trusted websites</p>
          <h1 id="api-domains-title">API Domains</h1>
          <p class="page-subtitle">Bind each user's existing API token to the website domain allowed to call the API.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="category-alert" role="status">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="category-alert error" role="alert">{{ $errors->first() }}</div>
      @endif

      <section class="panel api-domain-panel" aria-labelledby="add-api-domain-title">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title" id="add-api-domain-title">Add API domain</h2>
            <p class="panel-caption">Choose the account whose API token should be accepted from this domain.</p>
          </div>
        </div>
        @if ($users->isEmpty())
          <p class="api-domain-empty">No accounts have an API token yet. Create API access from the Doctors or Hospitals list first.</p>
        @else
          <form class="api-domain-form" method="POST" action="{{ route('api-domains.store') }}">
            @csrf
            <div class="api-domain-field">
              <label for="api-domain-user">API account / token</label>
              <select id="api-domain-user" name="user_id" required>
                <option value="">Select an account</option>
                @foreach ($users as $user)
                  <option value="{{ $user->id }}" @selected((string) old('user_id') === (string) $user->id)>
                    {{ $user->name }} — {{ $user->email }} ({{ $user->role === 1 ? 'Hospital' : 'Doctor' }})
                  </option>
                @endforeach
              </select>
              @error('user_id')<span class="api-domain-error">{{ $message }}</span>@enderror
            </div>
            <div class="api-domain-field">
              <label for="api-domain-name">Domain</label>
              <input id="api-domain-name" type="text" name="domain" value="{{ old('domain') }}" maxlength="253" placeholder="example.com" autocomplete="off" required>
              <span class="api-domain-hint">Enter hostname only—no https://, path, or port. Subdomains are separate domains.</span>
              @error('domain')<span class="api-domain-error">{{ $message }}</span>@enderror
            </div>
            <button class="category-save api-domain-submit" type="submit"><i class="fas fa-plus"></i> Add domain</button>
          </form>
        @endif
      </section>

      <section class="panel api-domain-panel" aria-labelledby="registered-api-domains-title">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title" id="registered-api-domains-title">Registered domains</h2>
            <p class="panel-caption">{{ $domains->count() }} {{ $domains->count() === 1 ? 'domain' : 'domains' }} bound to API accounts</p>
          </div>
        </div>
        <div class="category-table-wrap">
          <table class="category-table">
            <thead>
              <tr>
                <th scope="col">Domain</th>
                <th scope="col">API account</th>
                <th scope="col">Account type</th>
                <th scope="col">API status</th>
                <th scope="col">Added</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($domains as $apiDomain)
                <tr>
                  <td><strong>{{ $apiDomain->domain }}</strong></td>
                  <td>{{ $apiDomain->user->name }}<br><span class="api-domain-hint">{{ $apiDomain->user->email }}</span></td>
                  <td>{{ $apiDomain->user->role === 1 ? 'Hospital' : 'Doctor' }}</td>
                  <td>
                    <form method="POST" action="{{ route('api-domains.status', $apiDomain) }}">
                      @csrf
                      @method('PATCH')
                      <button
                        class="api-domain-switch {{ $apiDomain->is_active ? 'active' : '' }}"
                        type="submit"
                        role="switch"
                        aria-checked="{{ $apiDomain->is_active ? 'true' : 'false' }}"
                        aria-label="{{ $apiDomain->is_active ? 'Deactivate' : 'Activate' }} API for {{ $apiDomain->domain }}"
                        title="{{ $apiDomain->is_active ? 'Click to deactivate this API domain' : 'Click to activate this API domain' }}"
                      ><span></span></button>
                      <span class="api-domain-state {{ $apiDomain->is_active ? 'active' : 'inactive' }}">{{ $apiDomain->is_active ? 'Active' : 'Inactive' }}</span>
                    </form>
                  </td>
                  <td class="category-date">{{ $apiDomain->created_at->format('M j, Y') }}</td>
                  <td>
                    <form method="POST" action="{{ route('api-domains.destroy', $apiDomain) }}" onsubmit="return confirm('Remove this domain? API requests from it will stop working.')">
                      @csrf
                      @method('DELETE')
                      <button class="category-icon-button deactivate" type="submit" title="Remove domain" aria-label="Remove {{ $apiDomain->domain }}"><i class="fas fa-trash-alt"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td class="category-empty" colspan="6"><i class="fas fa-globe"></i> No API domains registered yet.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </section>

      <div class="category-alert api-domain-note">
        API requests must include <code>Authorization: Bearer YOUR_TOKEN</code> and an <code>Origin</code> header matching this domain. Server-to-server requests without an Origin or Referer are not accepted.
      </div>
    </div>
  </div>
</main>

<style>
  .api-domain-panel { margin-bottom: 20px; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .api-domain-form { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto; align-items: end; gap: 14px; padding: 0 20px 20px; }
  .api-domain-field { min-width: 0; display: grid; gap: 7px; }
  .api-domain-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .api-domain-field input, .api-domain-field select { width: 100%; min-height: 42px; padding: 10px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .api-domain-field input:focus, .api-domain-field select:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .api-domain-hint { color: var(--muted); font-size: 10px; }
  .api-domain-error { color: #a34d40; font-size: 11px; }
  .api-domain-submit { min-height: 42px; white-space: nowrap; }
  .api-domain-empty { margin: 0; padding: 0 20px 20px; color: var(--muted); font-size: 12px; }
  .api-domain-note { font-size: 12px; line-height: 1.7; }
  .api-domain-note code { overflow-wrap: anywhere; }
  .api-domain-switch { width: 42px; height: 24px; display: inline-flex; align-items: center; padding: 3px; vertical-align: middle; background: #c8cec7; border: 0; border-radius: 20px; cursor: pointer; transition: background .18s ease; }
  .api-domain-switch span { width: 18px; height: 18px; display: block; background: #fff; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,.2); transition: transform .18s ease; }
  .api-domain-switch.active { background: var(--green); }
  .api-domain-switch.active span { transform: translateX(18px); }
  .api-domain-switch:focus-visible { outline: 3px solid rgba(84,116,93,.3); outline-offset: 2px; }
  .api-domain-state { margin-left: 7px; font-size: 11px; font-weight: 700; }
  .api-domain-state.active { color: #2f7048; }
  .api-domain-state.inactive { color: #a34d40; }
  @media (max-width: 760px) { .api-domain-form { grid-template-columns: 1fr; } }
</style>
@endsection
