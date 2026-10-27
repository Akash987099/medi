@extends('users.layout.app')

@section('title', 'CMS Pages')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>CMS Pages</div>
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
      <section class="page-heading" aria-labelledby="cms-pages-title">
        <div>
          <p class="eyebrow">Manage your page content</p>
          <h1 id="cms-pages-title">CMS Pages</h1>
          <p class="page-subtitle">Create and manage standalone pages with their own URL slug, content and optional image.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="cms-page-alert" role="status">{{ session('status') }}</div>
      @endif

      <div class="cms-page-layout">
        <section class="panel cms-page-form-panel" aria-labelledby="create-cms-page-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="create-cms-page-title">Create a page</h2>
              <p class="panel-caption">Each CMS page is saved to your account.</p>
            </div>
          </div>
          <form class="cms-page-form" method="POST" action="{{ route('user.cms-pages.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="cms-page-field">
              <label for="cms-page-title">Page title</label>
              <input id="cms-page-title" type="text" name="title" value="{{ old('title') }}" maxlength="200" placeholder="e.g. About our clinic" required>
              @error('title')<span class="cms-page-error">{{ $message }}</span>@enderror
            </div>
            <div class="cms-page-field">
              <label for="cms-page-slug">URL slug <span>(optional; generated from title if blank)</span></label>
              <div class="cms-page-slug-input"><span>/</span><input id="cms-page-slug" type="text" name="slug" value="{{ old('slug') }}" maxlength="180" pattern="[A-Za-z0-9_-]+" placeholder="about-our-clinic"></div>
              @error('slug')<span class="cms-page-error">{{ $message }}</span>@enderror
            </div>
            <div class="cms-page-field">
              <label for="cms-page-content">Page content</label>
              <textarea id="cms-page-content" name="content" rows="9" maxlength="50000" placeholder="Write the content for your page..." required>{{ old('content') }}</textarea>
              @error('content')<span class="cms-page-error">{{ $message }}</span>@enderror
            </div>
            <div class="cms-page-field">
              <label for="cms-page-image">Page image <span>(optional, JPG/PNG/WebP, max 5 MB)</span></label>
              <input class="cms-page-file" id="cms-page-image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
              @error('image')<span class="cms-page-error">{{ $message }}</span>@enderror
            </div>
            <button class="cms-page-submit" type="submit"><i class="fas fa-plus"></i> Create page</button>
          </form>
        </section>

        <section class="panel cms-page-list-panel" aria-labelledby="saved-cms-pages-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="saved-cms-pages-title">Your pages</h2>
              <p class="panel-caption">{{ $pages->total() }} {{ \Illuminate\Support\Str::plural('page', $pages->total()) }} in your workspace</p>
            </div>
          </div>
          <form class="cms-page-filter" method="GET" action="{{ route('user.cms-pages.index') }}">
            <label class="cms-page-search-field" for="cms-page-search">
              <i class="fas fa-search" aria-hidden="true"></i>
              <input id="cms-page-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Find by title, slug or content">
            </label>
            <label class="cms-page-filter-status" for="cms-page-status-filter">
              <span class="sr-only">Filter by status</span>
              <select id="cms-page-status-filter" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
              </select>
            </label>
            <button class="cms-page-filter-submit" type="submit">Find</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
              <a class="cms-page-filter-reset" href="{{ route('user.cms-pages.index') }}">Clear</a>
            @endif
          </form>
          <div class="cms-page-list-scroll">
            @forelse ($pages as $page)
              <article class="cms-page-card">
                @if ($page->image_url)
                  <div class="cms-page-image-wrap"><img src="{{ $page->image_url }}" alt="{{ $page->title }}" loading="lazy"></div>
                @endif
                <div class="cms-page-card-content">
                  <div class="cms-page-card-heading">
                    <div>
                      <h3>{{ $page->title }}</h3>
                      <span class="cms-page-slug">/{{ $page->slug }}</span>
                      <span class="cms-page-date">{{ $page->created_at->format('M j, Y') }}</span>
                    </div>
                    <div class="cms-page-actions">
                      <span class="cms-page-status {{ $page->is_active ? 'active' : 'inactive' }}">{{ $page->is_active ? 'Active' : 'Inactive' }}</span>
                      <button class="cms-page-edit-button" type="button" data-open-cms-page-edit="cms-page-edit-dialog-{{ $page->id }}" aria-label="Edit {{ $page->title }}"><i class="fas fa-pen"></i></button>
                      <dialog class="cms-page-edit-dialog" id="cms-page-edit-dialog-{{ $page->id }}" aria-labelledby="cms-page-edit-title-{{ $page->id }}">
                        <div class="cms-page-edit-heading">
                          <div><h2 id="cms-page-edit-title-{{ $page->id }}">Edit CMS page</h2><p>Update page details and content.</p></div>
                          <button class="cms-page-edit-close" type="button" data-close-cms-page-edit aria-label="Close edit page"><i class="fas fa-times"></i></button>
                        </div>
                        <form class="cms-page-edit-form" method="POST" action="{{ route('user.cms-pages.update', $page->id) }}" enctype="multipart/form-data">
                          @csrf
                          @method('PUT')
                          @if ($page->image_url)
                            <img class="cms-page-edit-preview" src="{{ $page->image_url }}" alt="{{ $page->title }}">
                          @endif
                          <div class="cms-page-field">
                            <label for="edit-cms-page-title-{{ $page->id }}">Page title</label>
                            <input id="edit-cms-page-title-{{ $page->id }}" type="text" name="title" value="{{ $page->title }}" maxlength="200" required>
                          </div>
                          <div class="cms-page-field">
                            <label for="edit-cms-page-slug-{{ $page->id }}">URL slug</label>
                            <div class="cms-page-slug-input"><span>/</span><input id="edit-cms-page-slug-{{ $page->id }}" type="text" name="slug" value="{{ $page->slug }}" maxlength="180" pattern="[A-Za-z0-9_-]+" required></div>
                          </div>
                          <div class="cms-page-field">
                            <label for="edit-cms-page-content-{{ $page->id }}">Page content</label>
                            <textarea id="edit-cms-page-content-{{ $page->id }}" name="content" rows="10" maxlength="50000" required>{{ $page->content }}</textarea>
                          </div>
                          <div class="cms-page-field">
                            <label for="edit-cms-page-image-{{ $page->id }}">Replace image <span>(optional)</span></label>
                            <input class="cms-page-file" id="edit-cms-page-image-{{ $page->id }}" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                          </div>
                          <button class="cms-page-submit" type="submit">Save changes</button>
                        </form>
                      </dialog>
                      <form method="POST" action="{{ route('user.cms-pages.status', $page->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="cms-page-status-button {{ $page->is_active ? 'deactivate' : 'activate' }}" type="submit">{{ $page->is_active ? 'Deactivate' : 'Activate' }}</button>
                      </form>
                      <form method="POST" action="{{ route('user.cms-pages.destroy', $page->id) }}" onsubmit="return confirm('Delete this CMS page permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="cms-page-delete-button" type="submit" aria-label="Delete {{ $page->title }}"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    </div>
                  </div>
                  <p class="cms-page-content-preview">{{ $page->content }}</p>
                </div>
              </article>
            @empty
              <div class="cms-page-empty">
                <span><i class="far fa-file-alt"></i></span>
                <h3>No CMS pages yet</h3>
                <p>Create a page to start organizing your workspace content.</p>
              </div>
            @endforelse
          </div>
          @if ($pages->hasPages())
            <div class="cms-page-pagination">{{ $pages->links('vendor.pagination.compact') }}</div>
          @endif
        </section>
      </div>
    </div>
  </div>
</main>

<style>
  .cms-page-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .cms-page-layout { display: grid; grid-template-columns: minmax(300px, .78fr) minmax(0, 1.22fr); align-items: start; gap: 20px; }
  .cms-page-form-panel, .cms-page-list-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .cms-page-form { display: grid; gap: 13px; padding: 0 20px 21px; }
  .cms-page-field { min-width: 0; display: grid; gap: 7px; }
  .cms-page-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .cms-page-field label span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .cms-page-field input:not([type=file]), .cms-page-field textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .cms-page-field input:focus, .cms-page-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .cms-page-field textarea { resize: vertical; line-height: 1.6; }
  .cms-page-slug-input { min-width: 0; display: flex; align-items: center; gap: 8px; padding-left: 11px; color: var(--muted); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; font-size: 12px; }
  .cms-page-slug-input input { width: 100%; min-width: 0; padding: 11px 12px 11px 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 12px; }
  .cms-page-file { width: 100%; padding: 9px; border: 1px solid #e2e5dc; border-radius: 8px; font: inherit; font-size: 11px; }
  .cms-page-error { color: #a34d40; font-size: 11px; }
  .cms-page-submit { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .cms-page-submit:hover { background: var(--green); }
  .cms-page-list-panel { display: flex; flex-direction: column; }
  .cms-page-filter { display: flex; align-items: center; gap: 8px; padding: 0 20px 14px; }
  .cms-page-search-field { min-width: 0; min-height: 37px; display: flex; flex: 1; align-items: center; gap: 8px; padding: 0 11px; color: #849084; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; }
  .cms-page-search-field input { width: 100%; min-width: 0; padding: 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 11px; }
  .cms-page-filter-status select { min-height: 37px; max-width: 130px; padding: 0 8px; color: #59665b; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 10px; }
  .cms-page-filter-submit, .cms-page-filter-reset { min-height: 37px; display: inline-flex; align-items: center; justify-content: center; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .cms-page-filter-reset { color: #68756a; background: #f1f3ed; }
  .cms-page-list-scroll { max-height: min(68vh, 720px); display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-content: start; gap: 12px; overflow-y: auto; overscroll-behavior: contain; padding: 0 16px 16px; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; }
  .cms-page-card { min-width: 0; overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
  .cms-page-image-wrap { height: 145px; overflow: hidden; background: #f0f2ec; }
  .cms-page-image-wrap img { width: 100%; height: 100%; display: block; object-fit: cover; }
  .cms-page-card-content { padding: 12px; }
  .cms-page-card-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 7px; }
  .cms-page-card-heading h3 { margin: 0 0 5px; font-size: 12px; line-height: 1.45; overflow-wrap: anywhere; }
  .cms-page-slug, .cms-page-date { display: block; color: var(--muted); font-size: 9px; overflow-wrap: anywhere; }
  .cms-page-date { margin-top: 3px; }
  .cms-page-actions { display: flex; align-items: center; flex-wrap: wrap; justify-content: flex-end; gap: 5px; }
  .cms-page-actions form { margin: 0; }
  .cms-page-status { padding: 5px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
  .cms-page-status.active { color: #365844; background: #e8f1e6; }
  .cms-page-status.inactive { color: #8b6557; background: #f5ece7; }
  .cms-page-edit-button, .cms-page-delete-button { width: 27px; height: 27px; display: grid; place-items: center; border-radius: 6px; cursor: pointer; font-size: 10px; }
  .cms-page-edit-button { color: #526255; background: #f1f3ed; border: 0; }
  .cms-page-status-button { min-height: 27px; padding: 0 7px; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font-size: 9px; font-weight: 700; white-space: nowrap; }
  .cms-page-status-button.deactivate { color: #8b6557; background: #fff; border-color: #ead9d0; }
  .cms-page-status-button.activate { color: #365844; background: #fff; border-color: #d5e6d2; }
  .cms-page-delete-button { color: #a34d40; background: #fff; border: 1px solid #ead9d0; }
  .cms-page-delete-button:hover { color: #fff; background: #a34d40; border-color: #a34d40; }
  .cms-page-content-preview { max-height: 100px; margin: 10px 0 0; overflow: hidden; color: #647064; font-size: 10px; line-height: 1.6; white-space: pre-line; overflow-wrap: anywhere; }
  .cms-page-edit-dialog { width: min(560px, calc(100vw - 32px)); max-height: min(88vh, 780px); padding: 0; overflow-y: auto; color: var(--ink); background: #fffefa; border: 1px solid var(--line); border-radius: 13px; box-shadow: 0 24px 70px rgba(34,48,38,.24); }
  .cms-page-edit-dialog::backdrop { background: rgba(28,40,32,.42); backdrop-filter: blur(2px); }
  .cms-page-edit-heading { position: sticky; z-index: 1; top: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 17px 20px; background: #fffefa; border-bottom: 1px solid var(--line); }
  .cms-page-edit-heading h2 { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
  .cms-page-edit-heading p { margin: 0; color: var(--muted); font-size: 11px; }
  .cms-page-edit-close { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #647064; background: #f1f3ed; border: 0; border-radius: 50%; cursor: pointer; }
  .cms-page-edit-form { display: grid; gap: 15px; padding: 18px 20px 20px; }
  .cms-page-edit-preview { width: 100%; max-height: 190px; object-fit: cover; border-radius: 9px; }
  .cms-page-empty { grid-column: 1 / -1; display: grid; justify-items: center; padding: 46px 20px; text-align: center; }
  .cms-page-empty > span { width: 46px; height: 46px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .cms-page-empty h3 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .cms-page-empty p { margin: 0; color: var(--muted); font-size: 12px; }
  .cms-page-pagination { display: flex; justify-content: center; padding: 12px 20px 18px; border-top: 1px solid var(--line); }
  .cms-page-pagination .pagination-links { display: flex; align-items: center; gap: 5px; }
  .cms-page-pagination .pagination-links a, .cms-page-pagination .pagination-links > span { min-width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: #68756a; font-size: 11px; }
  .cms-page-pagination .pagination-links [aria-current=page] { color: #fff; background: var(--green); border-color: var(--green); }
  .cms-page-pagination svg { width: 15px; height: 15px; }
  @media (max-width: 850px) { .cms-page-layout { grid-template-columns: 1fr; } }
  @media (max-width: 560px) { .cms-page-list-scroll { grid-template-columns: 1fr; } .cms-page-filter { flex-wrap: wrap; } .cms-page-search-field { flex-basis: 100%; } }
</style>
<script>
  document.querySelectorAll('[data-open-cms-page-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.openCmsPageEdit);
      if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-cms-page-edit]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
</script>
@endsection
