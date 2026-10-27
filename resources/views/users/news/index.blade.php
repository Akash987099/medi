@extends('users.layout.app')

@section('title', 'News')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>News</div>
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
      <section class="page-heading" aria-labelledby="news-title">
        <div>
          <p class="eyebrow">Updates from your care team</p>
          <h1 id="news-title">News</h1>
          <p class="page-subtitle">Share announcements and updates with a photo. News posts use images, not videos.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="news-alert" role="status">{{ session('status') }}</div>
      @endif

      <div class="news-layout">
        <section class="panel news-form-panel" aria-labelledby="add-news-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="add-news-title">Publish an update</h2>
              <p class="panel-caption">Add a headline, story and image for your news.</p>
            </div>
          </div>
          <form class="news-form" method="POST" action="{{ route('user.news.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="news-field">
              <label for="news-title-input">Headline</label>
              <input id="news-title-input" type="text" name="title" value="{{ old('title') }}" maxlength="160" placeholder="e.g. New clinic hours this week" required>
              @error('title')<span class="news-error">{{ $message }}</span>@enderror
            </div>
            <div class="news-field">
              <label for="news-content-input">News story</label>
              <textarea id="news-content-input" name="content" rows="6" maxlength="10000" placeholder="Write your announcement or update..." required>{{ old('content') }}</textarea>
              @error('content')<span class="news-error">{{ $message }}</span>@enderror
            </div>
            <label class="news-upload-area" for="news-image">
              <span class="news-upload-icon"><i class="far fa-image"></i></span>
              <strong>Choose a news image</strong>
              <span>JPG, PNG or WebP · Max 5 MB</span>
              <input id="news-image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
            </label>
            @error('image')<span class="news-error">{{ $message }}</span>@enderror
            <p class="news-file-name" data-news-file-name>No image selected</p>
            <button class="news-submit" type="submit"><i class="fas fa-plus"></i> Publish news</button>
          </form>
        </section>

        <section class="panel news-list-panel" aria-labelledby="saved-news-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="saved-news-title">News articles</h2>
              <p class="panel-caption">{{ $newsItems->total() }} {{ \Illuminate\Support\Str::plural('article', $newsItems->total()) }} in your workspace</p>
            </div>
          </div>
          <form class="news-filter" method="GET" action="{{ route('user.news.index') }}">
            <label class="news-search-field" for="news-search">
              <i class="fas fa-search" aria-hidden="true"></i>
              <input id="news-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Find by headline or story">
            </label>
            <label class="news-filter-status" for="news-status-filter">
              <span class="sr-only">Filter by status</span>
              <select id="news-status-filter" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
              </select>
            </label>
            <button class="news-filter-submit" type="submit">Find</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
              <a class="news-filter-reset" href="{{ route('user.news.index') }}">Clear</a>
            @endif
          </form>
          <div class="news-list-scroll">
            @forelse ($newsItems as $news)
              <article class="news-card">
                <div class="news-image-wrap">
                  <img src="{{ $news->image_url }}" alt="{{ $news->title }}" loading="lazy">
                  <span class="news-card-status {{ $news->is_active ? 'active' : 'inactive' }}">{{ $news->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="news-card-content">
                  <div class="news-card-heading">
                    <div><h3>{{ $news->title }}</h3><span>{{ $news->created_at->format('M j, Y') }}</span></div>
                    <div class="news-actions">
                      <button class="news-edit-button" type="button" data-open-news-edit="news-edit-dialog-{{ $news->id }}" aria-label="Edit {{ $news->title }}"><i class="fas fa-pen"></i></button>
                      <dialog class="news-edit-dialog" id="news-edit-dialog-{{ $news->id }}" aria-labelledby="news-edit-title-{{ $news->id }}">
                        <div class="news-edit-heading">
                          <div><h2 id="news-edit-title-{{ $news->id }}">Edit news article</h2><p>Update the story or replace its image.</p></div>
                          <button class="news-edit-close" type="button" data-close-news-edit aria-label="Close edit news"><i class="fas fa-times"></i></button>
                        </div>
                        <form class="news-edit-form" method="POST" action="{{ route('user.news.update', $news->id) }}" enctype="multipart/form-data">
                          @csrf
                          @method('PUT')
                          <img class="news-edit-preview" src="{{ $news->image_url }}" alt="{{ $news->title }}">
                          <div class="news-field">
                            <label for="edit-news-title-{{ $news->id }}">Headline</label>
                            <input id="edit-news-title-{{ $news->id }}" type="text" name="title" value="{{ $news->title }}" maxlength="160" required>
                          </div>
                          <div class="news-field">
                            <label for="edit-news-content-{{ $news->id }}">News story</label>
                            <textarea id="edit-news-content-{{ $news->id }}" name="content" rows="6" maxlength="10000" required>{{ $news->content }}</textarea>
                          </div>
                          <div class="news-field">
                            <label for="edit-news-image-{{ $news->id }}">Replace image <span>(optional)</span></label>
                            <input class="news-file-input" id="edit-news-image-{{ $news->id }}" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <span class="news-file-hint">JPG, PNG or WebP · Max 5 MB</span>
                          </div>
                          <button class="news-submit" type="submit">Save changes</button>
                        </form>
                      </dialog>
                      <form method="POST" action="{{ route('user.news.status', $news->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="news-status-button {{ $news->is_active ? 'deactivate' : 'activate' }}" type="submit">{{ $news->is_active ? 'Deactivate' : 'Activate' }}</button>
                      </form>
                      <form method="POST" action="{{ route('user.news.destroy', $news->id) }}" onsubmit="return confirm('Delete this news article permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="news-delete-button" type="submit" aria-label="Delete {{ $news->title }}"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    </div>
                  </div>
                  <p class="news-card-story">{{ $news->content }}</p>
                </div>
              </article>
            @empty
              <div class="news-empty">
                <span><i class="far fa-newspaper"></i></span>
                <h3>No news articles yet</h3>
                <p>Publish a photo update to share news with your audience.</p>
              </div>
            @endforelse
          </div>
          @if ($newsItems->hasPages())
            <div class="news-pagination">{{ $newsItems->links('vendor.pagination.compact') }}</div>
          @endif
        </section>
      </div>
    </div>
  </div>
</main>

<style>
  .news-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .news-layout { display: grid; grid-template-columns: minmax(290px, .78fr) minmax(0, 1.22fr); align-items: start; gap: 20px; }
  .news-form-panel, .news-list-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .news-form { display: grid; gap: 13px; padding: 0 20px 21px; }
  .news-field { min-width: 0; display: grid; gap: 7px; }
  .news-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .news-field label span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .news-field input:not([type=file]), .news-field textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .news-field input:focus, .news-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .news-field textarea { resize: vertical; line-height: 1.6; }
  .news-upload-area { min-height: 132px; display: grid; justify-items: center; align-content: center; gap: 6px; padding: 14px; color: #536658; background: #f8f9f5; border: 1px dashed #cdd8c9; border-radius: 10px; cursor: pointer; text-align: center; }
  .news-upload-area:hover { background: #f0f4ed; border-color: #93aa93; }
  .news-upload-area input { width: min(100%, 230px); margin-top: 3px; color: #718071; font-size: 10px; }
  .news-upload-icon { width: 34px; height: 34px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .news-upload-area strong { font-size: 11px; }
  .news-upload-area > span:last-of-type, .news-file-name, .news-file-hint { color: var(--muted); font-size: 10px; }
  .news-file-name { min-height: 12px; margin: -8px 0 0; }
  .news-error { color: #a34d40; font-size: 11px; }
  .news-submit { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .news-submit:hover { background: var(--green); }
  .news-list-panel { display: flex; flex-direction: column; }
  .news-filter { display: flex; align-items: center; gap: 8px; padding: 0 20px 14px; }
  .news-search-field { min-width: 0; min-height: 37px; display: flex; flex: 1; align-items: center; gap: 8px; padding: 0 11px; color: #849084; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; }
  .news-search-field input { width: 100%; min-width: 0; padding: 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 11px; }
  .news-filter-status select { min-height: 37px; max-width: 130px; padding: 0 8px; color: #59665b; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 10px; }
  .news-filter-submit, .news-filter-reset { min-height: 37px; display: inline-flex; align-items: center; justify-content: center; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .news-filter-reset { color: #68756a; background: #f1f3ed; }
  .news-list-scroll { max-height: min(68vh, 720px); display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-content: start; gap: 12px; overflow-y: auto; overscroll-behavior: contain; padding: 0 16px 16px; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; }
  .news-card { min-width: 0; overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
  .news-image-wrap { position: relative; height: 155px; overflow: hidden; background: #f0f2ec; }
  .news-image-wrap img { width: 100%; height: 100%; display: block; object-fit: cover; }
  .news-card-status { position: absolute; top: 9px; left: 9px; padding: 5px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
  .news-card-status.active { color: #365844; background: #e8f1e6; }
  .news-card-status.inactive { color: #8b6557; background: #f5ece7; }
  .news-card-content { padding: 11px; }
  .news-card-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 7px; }
  .news-card-heading h3 { margin: 0 0 4px; font-size: 11px; line-height: 1.45; overflow-wrap: anywhere; }
  .news-card-heading > div:first-child > span { color: var(--muted); font-size: 9px; }
  .news-actions { display: flex; align-items: center; flex-wrap: wrap; justify-content: flex-end; gap: 5px; }
  .news-actions form { margin: 0; }
  .news-edit-button, .news-delete-button { width: 27px; height: 27px; display: grid; place-items: center; border-radius: 6px; cursor: pointer; font-size: 10px; }
  .news-edit-button { color: #526255; background: #f1f3ed; border: 0; }
  .news-status-button { min-height: 27px; padding: 0 7px; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font-size: 9px; font-weight: 700; white-space: nowrap; }
  .news-status-button.deactivate { color: #8b6557; background: #fff; border-color: #ead9d0; }
  .news-status-button.activate { color: #365844; background: #fff; border-color: #d5e6d2; }
  .news-delete-button { color: #a34d40; background: #fff; border: 1px solid #ead9d0; }
  .news-delete-button:hover { color: #fff; background: #a34d40; border-color: #a34d40; }
  .news-card-story { max-height: 105px; margin: 9px 0 0; overflow: hidden; color: #647064; font-size: 10px; line-height: 1.55; white-space: pre-line; overflow-wrap: anywhere; }
  .news-edit-dialog { width: min(480px, calc(100vw - 32px)); max-height: min(88vh, 780px); padding: 0; overflow-y: auto; color: var(--ink); background: #fffefa; border: 1px solid var(--line); border-radius: 13px; box-shadow: 0 24px 70px rgba(34,48,38,.24); }
  .news-edit-dialog::backdrop { background: rgba(28,40,32,.42); backdrop-filter: blur(2px); }
  .news-edit-heading { position: sticky; z-index: 1; top: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 17px 20px; background: #fffefa; border-bottom: 1px solid var(--line); }
  .news-edit-heading h2 { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
  .news-edit-heading p { margin: 0; color: var(--muted); font-size: 11px; }
  .news-edit-close { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #647064; background: #f1f3ed; border: 0; border-radius: 50%; cursor: pointer; }
  .news-edit-form { display: grid; gap: 15px; padding: 18px 20px 20px; }
  .news-edit-preview { width: 100%; max-height: 190px; object-fit: cover; border-radius: 9px; }
  .news-file-input { width: 100%; padding: 9px; border: 1px solid #e2e5dc; border-radius: 8px; font: inherit; font-size: 11px; }
  .news-empty { grid-column: 1 / -1; display: grid; justify-items: center; padding: 46px 20px; text-align: center; }
  .news-empty > span { width: 46px; height: 46px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .news-empty h3 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .news-empty p { margin: 0; color: var(--muted); font-size: 12px; }
  .news-pagination { display: flex; justify-content: center; padding: 12px 20px 18px; border-top: 1px solid var(--line); }
  .news-pagination .pagination-links { display: flex; align-items: center; gap: 5px; }
  .news-pagination .pagination-links a, .news-pagination .pagination-links > span { min-width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: #68756a; font-size: 11px; }
  .news-pagination .pagination-links [aria-current=page] { color: #fff; background: var(--green); border-color: var(--green); }
  .news-pagination svg { width: 15px; height: 15px; }
  @media (max-width: 850px) { .news-layout { grid-template-columns: 1fr; } }
  @media (max-width: 560px) { .news-list-scroll { grid-template-columns: 1fr; } .news-filter { flex-wrap: wrap; } .news-search-field { flex-basis: 100%; } .news-image-wrap { height: 190px; } }
</style>
<script>
  document.querySelectorAll('[data-open-news-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.openNewsEdit);
      if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-news-edit]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
  const newsImageInput = document.querySelector('#news-image');
  const newsFileName = document.querySelector('[data-news-file-name]');
  if (newsImageInput && newsFileName) {
    newsImageInput.addEventListener('change', () => {
      newsFileName.textContent = newsImageInput.files?.[0]?.name || 'No image selected';
    });
  }
</script>
@endsection
