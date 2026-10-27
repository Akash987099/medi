@extends('users.layout.app')

@section('title', 'Social Media')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Social Media</div>
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
      <section class="page-heading" aria-labelledby="social-media-title">
        <div>
          <p class="eyebrow">Stay connected with your community</p>
          <h1 id="social-media-title">Social Media</h1>
          <p class="page-subtitle">Manage social profile links for your account. These links are private to your workspace.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="social-media-alert" role="status">{{ session('status') }}</div>
      @endif

      <div class="social-media-layout">
        <section class="panel social-media-form-panel" aria-labelledby="add-social-link-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="add-social-link-title">Add a profile link</h2>
              <p class="panel-caption">Enter a platform name and its secure HTTPS profile URL.</p>
            </div>
          </div>
          <form class="social-media-form" method="POST" action="{{ route('user.social-media.store') }}">
            @csrf
            <div class="social-media-field">
              <label for="social-platform">Platform</label>
              <input id="social-platform" type="text" name="platform" value="{{ old('platform') }}" maxlength="80" placeholder="e.g. Facebook, Instagram, LinkedIn" required>
              @error('platform')<span class="social-media-error">{{ $message }}</span>@enderror
            </div>
            <div class="social-media-field">
              <label for="social-url">Profile URL</label>
              <input id="social-url" type="url" name="url" value="{{ old('url') }}" maxlength="2048" placeholder="https://..." autocomplete="url" required>
              @error('url')<span class="social-media-error">{{ $message }}</span>@enderror
            </div>
            <button class="social-media-submit" type="submit"><i class="fas fa-plus"></i> Add social link</button>
          </form>
        </section>

        <section class="panel social-media-list-panel" aria-labelledby="saved-social-links-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="saved-social-links-title">Your social profiles</h2>
              <p class="panel-caption">{{ $socialLinks->total() }} {{ \Illuminate\Support\Str::plural('profile', $socialLinks->total()) }} in your workspace</p>
            </div>
          </div>
          <form class="social-media-filter" method="GET" action="{{ route('user.social-media.index') }}">
            <label class="social-media-search" for="social-media-search">
              <i class="fas fa-search" aria-hidden="true"></i>
              <input id="social-media-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Find by platform or URL">
            </label>
            <label class="social-media-status-filter" for="social-media-status-filter">
              <span class="sr-only">Filter by status</span>
              <select id="social-media-status-filter" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
              </select>
            </label>
            <button class="social-media-filter-submit" type="submit">Find</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
              <a class="social-media-filter-reset" href="{{ route('user.social-media.index') }}">Clear</a>
            @endif
          </form>
          <div class="social-media-list-scroll">
            @forelse ($socialLinks as $link)
              @php
                $platformIcon = match (\Illuminate\Support\Str::lower($link->platform)) {
                  'facebook' => 'fab fa-facebook-f',
                  'instagram' => 'fab fa-instagram',
                  'linkedin' => 'fab fa-linkedin-in',
                  'youtube' => 'fab fa-youtube',
                  'x', 'twitter' => 'fab fa-x-twitter',
                  'whatsapp' => 'fab fa-whatsapp',
                  default => 'fas fa-share-alt',
                };
              @endphp
              <article class="social-media-card">
                <div class="social-media-icon"><i class="{{ $platformIcon }}" aria-hidden="true"></i></div>
                <div class="social-media-card-content">
                  <div class="social-media-card-heading">
                    <div>
                      <h3>{{ $link->platform }}</h3>
                      <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer">{{ $link->url }}</a>
                    </div>
                    <span class="social-media-card-status {{ $link->is_active ? 'active' : 'inactive' }}">{{ $link->is_active ? 'Active' : 'Inactive' }}</span>
                  </div>
                  <div class="social-media-actions">
                    <button class="social-media-edit-button" type="button" data-open-social-edit="social-edit-dialog-{{ $link->id }}" aria-label="Edit {{ $link->platform }}"><i class="fas fa-pen"></i> Edit</button>
                    <dialog class="social-media-edit-dialog" id="social-edit-dialog-{{ $link->id }}" aria-labelledby="social-edit-title-{{ $link->id }}">
                      <div class="social-media-edit-heading">
                        <div><h2 id="social-edit-title-{{ $link->id }}">Edit social profile</h2><p>Update the platform name and secure profile URL.</p></div>
                        <button class="social-media-edit-close" type="button" data-close-social-edit aria-label="Close edit social profile"><i class="fas fa-times"></i></button>
                      </div>
                      <form class="social-media-edit-form" method="POST" action="{{ route('user.social-media.update', $link->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="social-media-field">
                          <label for="edit-social-platform-{{ $link->id }}">Platform</label>
                          <input id="edit-social-platform-{{ $link->id }}" type="text" name="platform" value="{{ $link->platform }}" maxlength="80" required>
                        </div>
                        <div class="social-media-field">
                          <label for="edit-social-url-{{ $link->id }}">Profile URL</label>
                          <input id="edit-social-url-{{ $link->id }}" type="url" name="url" value="{{ $link->url }}" maxlength="2048" required>
                        </div>
                        <button class="social-media-submit" type="submit">Save changes</button>
                      </form>
                    </dialog>
                    <form method="POST" action="{{ route('user.social-media.status', $link->id) }}">
                      @csrf
                      @method('PATCH')
                      <button class="social-media-status-button {{ $link->is_active ? 'deactivate' : 'activate' }}" type="submit">{{ $link->is_active ? 'Deactivate' : 'Activate' }}</button>
                    </form>
                    <form method="POST" action="{{ route('user.social-media.destroy', $link->id) }}" onsubmit="return confirm('Delete this social media link permanently?')">
                      @csrf
                      @method('DELETE')
                      <button class="social-media-delete-button" type="submit" aria-label="Delete {{ $link->platform }}"><i class="fas fa-trash-alt"></i></button>
                    </form>
                  </div>
                </div>
              </article>
            @empty
              <div class="social-media-empty">
                <span><i class="fas fa-share-alt"></i></span>
                <h3>No social profiles yet</h3>
                <p>Add links to your clinic or professional social profiles.</p>
              </div>
            @endforelse
          </div>
          @if ($socialLinks->hasPages())
            <div class="social-media-pagination">{{ $socialLinks->links('vendor.pagination.compact') }}</div>
          @endif
        </section>
      </div>
    </div>
  </div>
</main>

<style>
  .social-media-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .social-media-layout { display: grid; grid-template-columns: minmax(280px, .72fr) minmax(0, 1.28fr); align-items: start; gap: 20px; }
  .social-media-form-panel, .social-media-list-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .social-media-form, .social-media-edit-form { display: grid; gap: 14px; padding: 0 20px 21px; }
  .social-media-field { min-width: 0; display: grid; gap: 7px; }
  .social-media-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .social-media-field input { width: 100%; min-height: 40px; padding: 10px 11px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .social-media-field input:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .social-media-error { color: #a34d40; font-size: 11px; }
  .social-media-submit { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .social-media-submit:hover { background: var(--green); }
  .social-media-list-panel { display: flex; flex-direction: column; }
  .social-media-filter { display: flex; align-items: center; gap: 8px; padding: 0 20px 14px; }
  .social-media-search { min-width: 0; min-height: 37px; display: flex; flex: 1; align-items: center; gap: 8px; padding: 0 11px; color: #849084; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; }
  .social-media-search input { width: 100%; min-width: 0; padding: 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 11px; }
  .social-media-status-filter select { min-height: 37px; max-width: 130px; padding: 0 8px; color: #59665b; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 10px; }
  .social-media-filter-submit, .social-media-filter-reset { min-height: 37px; display: inline-flex; align-items: center; justify-content: center; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .social-media-filter-reset { color: #68756a; background: #f1f3ed; }
  .social-media-list-scroll { max-height: min(68vh, 720px); display: grid; align-content: start; gap: 10px; overflow-y: auto; overscroll-behavior: contain; padding: 0 16px 16px; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; }
  .social-media-card { min-width: 0; display: flex; align-items: flex-start; gap: 13px; padding: 14px; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
  .social-media-icon { width: 40px; height: 40px; flex: 0 0 40px; display: grid; place-items: center; color: var(--green-dark); background: var(--sage); border-radius: 10px; font-size: 17px; }
  .social-media-card-content { min-width: 0; flex: 1; }
  .social-media-card-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; }
  .social-media-card-heading h3 { margin: 0 0 4px; color: var(--ink); font-size: 12px; }
  .social-media-card-heading a { display: block; max-width: 100%; color: #68756a; font-size: 10px; overflow-wrap: anywhere; text-decoration: none; }
  .social-media-card-heading a:hover { color: var(--green-dark); text-decoration: underline; }
  .social-media-card-status { flex: 0 0 auto; padding: 5px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
  .social-media-card-status.active { color: #365844; background: #e8f1e6; }
  .social-media-card-status.inactive { color: #8b6557; background: #f5ece7; }
  .social-media-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; margin-top: 11px; }
  .social-media-actions form { margin: 0; }
  .social-media-edit-button, .social-media-status-button, .social-media-delete-button { min-height: 28px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 0 9px; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font-size: 9px; font-weight: 700; }
  .social-media-edit-button { color: #526255; background: #f1f3ed; }
  .social-media-status-button.deactivate { color: #8b6557; background: #fff; border-color: #ead9d0; }
  .social-media-status-button.activate { color: #365844; background: #fff; border-color: #d5e6d2; }
  .social-media-delete-button { color: #a34d40; background: #fff; border-color: #ead9d0; }
  .social-media-delete-button:hover { color: #fff; background: #a34d40; border-color: #a34d40; }
  .social-media-edit-dialog { width: min(460px, calc(100vw - 32px)); max-height: min(88vh, 700px); padding: 0; overflow-y: auto; color: var(--ink); background: #fffefa; border: 1px solid var(--line); border-radius: 13px; box-shadow: 0 24px 70px rgba(34,48,38,.24); }
  .social-media-edit-dialog::backdrop { background: rgba(28,40,32,.42); backdrop-filter: blur(2px); }
  .social-media-edit-heading { position: sticky; z-index: 1; top: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 17px 20px; background: #fffefa; border-bottom: 1px solid var(--line); }
  .social-media-edit-heading h2 { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
  .social-media-edit-heading p { margin: 0; color: var(--muted); font-size: 11px; }
  .social-media-edit-close { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #647064; background: #f1f3ed; border: 0; border-radius: 50%; cursor: pointer; }
  .social-media-edit-form { padding-top: 18px; }
  .social-media-empty { display: grid; justify-items: center; padding: 46px 20px; text-align: center; }
  .social-media-empty > span { width: 46px; height: 46px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .social-media-empty h3 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .social-media-empty p { margin: 0; color: var(--muted); font-size: 12px; }
  .social-media-pagination { display: flex; justify-content: center; padding: 12px 20px 18px; border-top: 1px solid var(--line); }
  .social-media-pagination .pagination-links { display: flex; align-items: center; gap: 5px; }
  .social-media-pagination .pagination-links a, .social-media-pagination .pagination-links > span { min-width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: #68756a; font-size: 11px; }
  .social-media-pagination .pagination-links [aria-current=page] { color: #fff; background: var(--green); border-color: var(--green); }
  .social-media-pagination svg { width: 15px; height: 15px; }
  @media (max-width: 850px) { .social-media-layout { grid-template-columns: 1fr; } }
  @media (max-width: 560px) { .social-media-filter { flex-wrap: wrap; } .social-media-search { flex-basis: 100%; } .social-media-card { padding: 11px; } .social-media-card-heading { flex-direction: column; } }
</style>
<script>
  document.querySelectorAll('[data-open-social-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.openSocialEdit);
      if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-social-edit]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
</script>
@endsection
