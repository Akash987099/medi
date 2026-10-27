@extends('users.layout.app')

@section('title', 'Articles')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Articles</div>
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
      <section class="page-heading" aria-labelledby="articles-title">
        <div>
          <p class="eyebrow">Knowledge and care stories</p>
          <h1 id="articles-title">Articles</h1>
          <p class="page-subtitle">Publish longer-form articles with a featured image. Articles are separate from News updates.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="article-alert" role="status">{{ session('status') }}</div>
      @endif

      <div class="article-layout">
        <section class="panel article-form-panel" aria-labelledby="create-article-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="create-article-title">Write an article</h2>
              <p class="panel-caption">Add a title, full article and featured image.</p>
            </div>
          </div>
          <form class="article-form" method="POST" action="{{ route('user.articles.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="article-field">
              <label for="article-title">Article title</label>
              <input id="article-title" type="text" name="title" value="{{ old('title') }}" maxlength="200" placeholder="e.g. How to prepare for your first visit" required>
              @error('title')<span class="article-error">{{ $message }}</span>@enderror
            </div>
            <div class="article-field">
              <label for="article-content">Article content</label>
              <textarea id="article-content" name="content" rows="10" maxlength="20000" placeholder="Write your full article here..." required>{{ old('content') }}</textarea>
              @error('content')<span class="article-error">{{ $message }}</span>@enderror
            </div>
            <label class="article-upload-area" for="article-image">
              <span class="article-upload-icon"><i class="far fa-image"></i></span>
              <strong>Choose a featured image</strong>
              <span>JPG, PNG or WebP · Max 5 MB</span>
              <input id="article-image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
            </label>
            @error('image')<span class="article-error">{{ $message }}</span>@enderror
            <p class="article-file-name" data-article-file-name>No image selected</p>
            <button class="article-submit" type="submit"><i class="fas fa-plus"></i> Publish article</button>
          </form>
        </section>

        <section class="panel article-list-panel" aria-labelledby="saved-articles-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="saved-articles-title">Saved articles</h2>
              <p class="panel-caption">{{ $articles->total() }} {{ \Illuminate\Support\Str::plural('article', $articles->total()) }} in your workspace</p>
            </div>
          </div>
          <form class="article-filter" method="GET" action="{{ route('user.articles.index') }}">
            <label class="article-search-field" for="article-search">
              <i class="fas fa-search" aria-hidden="true"></i>
              <input id="article-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Find by article title or content">
            </label>
            <label class="article-filter-status" for="article-status-filter">
              <span class="sr-only">Filter by status</span>
              <select id="article-status-filter" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
              </select>
            </label>
            <button class="article-filter-submit" type="submit">Find</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
              <a class="article-filter-reset" href="{{ route('user.articles.index') }}">Clear</a>
            @endif
          </form>
          <div class="article-list-scroll">
            @forelse ($articles as $article)
              <article class="article-card">
                <div class="article-image-wrap">
                  <img src="{{ $article->image_url }}" alt="{{ $article->title }}" loading="lazy">
                  <span class="article-card-status {{ $article->is_active ? 'active' : 'inactive' }}">{{ $article->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="article-card-content">
                  <div class="article-card-heading">
                    <div><h3>{{ $article->title }}</h3><span>{{ $article->created_at->format('M j, Y') }}</span></div>
                    <div class="article-actions">
                      <button class="article-edit-button" type="button" data-open-article-edit="article-edit-dialog-{{ $article->id }}" aria-label="Edit {{ $article->title }}"><i class="fas fa-pen"></i></button>
                      <dialog class="article-edit-dialog" id="article-edit-dialog-{{ $article->id }}" aria-labelledby="article-edit-title-{{ $article->id }}">
                        <div class="article-edit-heading">
                          <div><h2 id="article-edit-title-{{ $article->id }}">Edit article</h2><p>Update the article or replace its image.</p></div>
                          <button class="article-edit-close" type="button" data-close-article-edit aria-label="Close edit article"><i class="fas fa-times"></i></button>
                        </div>
                        <form class="article-edit-form" method="POST" action="{{ route('user.articles.update', $article->id) }}" enctype="multipart/form-data">
                          @csrf
                          @method('PUT')
                          <img class="article-edit-preview" src="{{ $article->image_url }}" alt="{{ $article->title }}">
                          <div class="article-field">
                            <label for="edit-article-title-{{ $article->id }}">Article title</label>
                            <input id="edit-article-title-{{ $article->id }}" type="text" name="title" value="{{ $article->title }}" maxlength="200" required>
                          </div>
                          <div class="article-field">
                            <label for="edit-article-content-{{ $article->id }}">Article content</label>
                            <textarea id="edit-article-content-{{ $article->id }}" name="content" rows="10" maxlength="20000" required>{{ $article->content }}</textarea>
                          </div>
                          <div class="article-field">
                            <label for="edit-article-image-{{ $article->id }}">Replace image <span>(optional)</span></label>
                            <input class="article-file-input" id="edit-article-image-{{ $article->id }}" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <span class="article-file-hint">JPG, PNG or WebP · Max 5 MB</span>
                          </div>
                          <button class="article-submit" type="submit">Save changes</button>
                        </form>
                      </dialog>
                      <form method="POST" action="{{ route('user.articles.status', $article->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="article-status-button {{ $article->is_active ? 'deactivate' : 'activate' }}" type="submit">{{ $article->is_active ? 'Deactivate' : 'Activate' }}</button>
                      </form>
                      <form method="POST" action="{{ route('user.articles.destroy', $article->id) }}" onsubmit="return confirm('Delete this article permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="article-delete-button" type="submit" aria-label="Delete {{ $article->title }}"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    </div>
                  </div>
                  <p class="article-card-copy">{{ $article->content }}</p>
                </div>
              </article>
            @empty
              <div class="article-empty">
                <span><i class="far fa-file-alt"></i></span>
                <h3>No articles yet</h3>
                <p>Write and publish an article with a featured image.</p>
              </div>
            @endforelse
          </div>
          @if ($articles->hasPages())
            <div class="article-pagination">{{ $articles->links('vendor.pagination.compact') }}</div>
          @endif
        </section>
      </div>
    </div>
  </div>
</main>

<style>
  .article-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .article-layout { display: grid; grid-template-columns: minmax(290px, .78fr) minmax(0, 1.22fr); align-items: start; gap: 20px; }
  .article-form-panel, .article-list-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .article-form { display: grid; gap: 13px; padding: 0 20px 21px; }
  .article-field { min-width: 0; display: grid; gap: 7px; }
  .article-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .article-field label span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .article-field input:not([type=file]), .article-field textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .article-field input:focus, .article-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .article-field textarea { resize: vertical; line-height: 1.6; }
  .article-upload-area { min-height: 132px; display: grid; justify-items: center; align-content: center; gap: 6px; padding: 14px; color: #536658; background: #f8f9f5; border: 1px dashed #cdd8c9; border-radius: 10px; cursor: pointer; text-align: center; }
  .article-upload-area:hover { background: #f0f4ed; border-color: #93aa93; }
  .article-upload-area input { width: min(100%, 230px); margin-top: 3px; color: #718071; font-size: 10px; }
  .article-upload-icon { width: 34px; height: 34px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .article-upload-area strong { font-size: 11px; }
  .article-upload-area > span:last-of-type, .article-file-name, .article-file-hint { color: var(--muted); font-size: 10px; }
  .article-file-name { min-height: 12px; margin: -8px 0 0; }
  .article-error { color: #a34d40; font-size: 11px; }
  .article-submit { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .article-submit:hover { background: var(--green); }
  .article-list-panel { display: flex; flex-direction: column; }
  .article-filter { display: flex; align-items: center; gap: 8px; padding: 0 20px 14px; }
  .article-search-field { min-width: 0; min-height: 37px; display: flex; flex: 1; align-items: center; gap: 8px; padding: 0 11px; color: #849084; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; }
  .article-search-field input { width: 100%; min-width: 0; padding: 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 11px; }
  .article-filter-status select { min-height: 37px; max-width: 130px; padding: 0 8px; color: #59665b; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 10px; }
  .article-filter-submit, .article-filter-reset { min-height: 37px; display: inline-flex; align-items: center; justify-content: center; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .article-filter-reset { color: #68756a; background: #f1f3ed; }
  .article-list-scroll { max-height: min(68vh, 720px); display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-content: start; gap: 12px; overflow-y: auto; overscroll-behavior: contain; padding: 0 16px 16px; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; }
  .article-card { min-width: 0; overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
  .article-image-wrap { position: relative; height: 155px; overflow: hidden; background: #f0f2ec; }
  .article-image-wrap img { width: 100%; height: 100%; display: block; object-fit: cover; }
  .article-card-status { position: absolute; top: 9px; left: 9px; padding: 5px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
  .article-card-status.active { color: #365844; background: #e8f1e6; }
  .article-card-status.inactive { color: #8b6557; background: #f5ece7; }
  .article-card-content { padding: 11px; }
  .article-card-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 7px; }
  .article-card-heading h3 { margin: 0 0 4px; font-size: 11px; line-height: 1.45; overflow-wrap: anywhere; }
  .article-card-heading > div:first-child > span { color: var(--muted); font-size: 9px; }
  .article-actions { display: flex; align-items: center; flex-wrap: wrap; justify-content: flex-end; gap: 5px; }
  .article-actions form { margin: 0; }
  .article-edit-button, .article-delete-button { width: 27px; height: 27px; display: grid; place-items: center; border-radius: 6px; cursor: pointer; font-size: 10px; }
  .article-edit-button { color: #526255; background: #f1f3ed; border: 0; }
  .article-status-button { min-height: 27px; padding: 0 7px; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font-size: 9px; font-weight: 700; white-space: nowrap; }
  .article-status-button.deactivate { color: #8b6557; background: #fff; border-color: #ead9d0; }
  .article-status-button.activate { color: #365844; background: #fff; border-color: #d5e6d2; }
  .article-delete-button { color: #a34d40; background: #fff; border: 1px solid #ead9d0; }
  .article-delete-button:hover { color: #fff; background: #a34d40; border-color: #a34d40; }
  .article-card-copy { max-height: 110px; margin: 9px 0 0; overflow: hidden; color: #647064; font-size: 10px; line-height: 1.6; white-space: pre-line; overflow-wrap: anywhere; }
  .article-edit-dialog { width: min(560px, calc(100vw - 32px)); max-height: min(88vh, 780px); padding: 0; overflow-y: auto; color: var(--ink); background: #fffefa; border: 1px solid var(--line); border-radius: 13px; box-shadow: 0 24px 70px rgba(34,48,38,.24); }
  .article-edit-dialog::backdrop { background: rgba(28,40,32,.42); backdrop-filter: blur(2px); }
  .article-edit-heading { position: sticky; z-index: 1; top: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 17px 20px; background: #fffefa; border-bottom: 1px solid var(--line); }
  .article-edit-heading h2 { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
  .article-edit-heading p { margin: 0; color: var(--muted); font-size: 11px; }
  .article-edit-close { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #647064; background: #f1f3ed; border: 0; border-radius: 50%; cursor: pointer; }
  .article-edit-form { display: grid; gap: 15px; padding: 18px 20px 20px; }
  .article-edit-preview { width: 100%; max-height: 190px; object-fit: cover; border-radius: 9px; }
  .article-file-input { width: 100%; padding: 9px; border: 1px solid #e2e5dc; border-radius: 8px; font: inherit; font-size: 11px; }
  .article-empty { grid-column: 1 / -1; display: grid; justify-items: center; padding: 46px 20px; text-align: center; }
  .article-empty > span { width: 46px; height: 46px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .article-empty h3 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .article-empty p { margin: 0; color: var(--muted); font-size: 12px; }
  .article-pagination { display: flex; justify-content: center; padding: 12px 20px 18px; border-top: 1px solid var(--line); }
  .article-pagination .pagination-links { display: flex; align-items: center; gap: 5px; }
  .article-pagination .pagination-links a, .article-pagination .pagination-links > span { min-width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: #68756a; font-size: 11px; }
  .article-pagination .pagination-links [aria-current=page] { color: #fff; background: var(--green); border-color: var(--green); }
  .article-pagination svg { width: 15px; height: 15px; }
  @media (max-width: 850px) { .article-layout { grid-template-columns: 1fr; } }
  @media (max-width: 560px) { .article-list-scroll { grid-template-columns: 1fr; } .article-filter { flex-wrap: wrap; } .article-search-field { flex-basis: 100%; } .article-image-wrap { height: 190px; } }
</style>
<script>
  document.querySelectorAll('[data-open-article-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.openArticleEdit);
      if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-article-edit]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
  const articleImageInput = document.querySelector('#article-image');
  const articleFileName = document.querySelector('[data-article-file-name]');
  if (articleImageInput && articleFileName) {
    articleImageInput.addEventListener('change', () => {
      articleFileName.textContent = articleImageInput.files?.[0]?.name || 'No image selected';
    });
  }
</script>
@endsection
