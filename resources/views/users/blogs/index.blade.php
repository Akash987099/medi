@extends('users.layout.app')

@section('title', 'Blogs')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Blogs</div>
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
      <section class="page-heading" aria-labelledby="blogs-title">
        <div>
          <p class="eyebrow">Stories and insights</p>
          <h1 id="blogs-title">Blogs</h1>
          <p class="page-subtitle">Share health insights and stories with a featured image. Blog posts are managed separately from News and Articles.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="blog-alert" role="status">{{ session('status') }}</div>
      @endif

      <div class="blog-layout">
        <section class="panel blog-form-panel" aria-labelledby="write-blog-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="write-blog-title">Write a blog post</h2>
              <p class="panel-caption">Add a title, full story and featured image.</p>
            </div>
          </div>
          <form class="blog-form" method="POST" action="{{ route('user.blogs.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="blog-field">
              <label for="blog-title">Blog title</label>
              <input id="blog-title" type="text" name="title" value="{{ old('title') }}" maxlength="200" placeholder="e.g. Simple ways to support better sleep" required>
              @error('title')<span class="blog-error">{{ $message }}</span>@enderror
            </div>
            <div class="blog-field">
              <label for="blog-content">Blog content</label>
              <textarea id="blog-content" name="content" rows="10" maxlength="20000" placeholder="Write your blog post here..." required>{{ old('content') }}</textarea>
              @error('content')<span class="blog-error">{{ $message }}</span>@enderror
            </div>
            <label class="blog-upload-area" for="blog-image">
              <span class="blog-upload-icon"><i class="far fa-image"></i></span>
              <strong>Choose a featured image</strong>
              <span>JPG, PNG or WebP · Max 5 MB</span>
              <input id="blog-image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
            </label>
            @error('image')<span class="blog-error">{{ $message }}</span>@enderror
            <p class="blog-file-name" data-blog-file-name>No image selected</p>
            <button class="blog-submit" type="submit"><i class="fas fa-plus"></i> Publish blog</button>
          </form>
        </section>

        <section class="panel blog-list-panel" aria-labelledby="saved-blogs-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="saved-blogs-title">Saved blog posts</h2>
              <p class="panel-caption">{{ $blogs->total() }} {{ \Illuminate\Support\Str::plural('post', $blogs->total()) }} in your workspace</p>
            </div>
          </div>
          <form class="blog-filter" method="GET" action="{{ route('user.blogs.index') }}">
            <label class="blog-search-field" for="blog-search">
              <i class="fas fa-search" aria-hidden="true"></i>
              <input id="blog-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Find by blog title or content">
            </label>
            <label class="blog-filter-status" for="blog-status-filter">
              <span class="sr-only">Filter by status</span>
              <select id="blog-status-filter" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
              </select>
            </label>
            <button class="blog-filter-submit" type="submit">Find</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
              <a class="blog-filter-reset" href="{{ route('user.blogs.index') }}">Clear</a>
            @endif
          </form>
          <div class="blog-list-scroll">
            @forelse ($blogs as $blog)
              <article class="blog-card">
                <div class="blog-image-wrap">
                  <img src="{{ $blog->image_url }}" alt="{{ $blog->title }}" loading="lazy">
                  <span class="blog-card-status {{ $blog->is_active ? 'active' : 'inactive' }}">{{ $blog->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="blog-card-content">
                  <div class="blog-card-heading">
                    <div><h3>{{ $blog->title }}</h3><span>{{ $blog->created_at->format('M j, Y') }}</span></div>
                    <div class="blog-actions">
                      <button class="blog-edit-button" type="button" data-open-blog-edit="blog-edit-dialog-{{ $blog->id }}" aria-label="Edit {{ $blog->title }}"><i class="fas fa-pen"></i></button>
                      <dialog class="blog-edit-dialog" id="blog-edit-dialog-{{ $blog->id }}" aria-labelledby="blog-edit-title-{{ $blog->id }}">
                        <div class="blog-edit-heading">
                          <div><h2 id="blog-edit-title-{{ $blog->id }}">Edit blog post</h2><p>Update your story or replace its featured image.</p></div>
                          <button class="blog-edit-close" type="button" data-close-blog-edit aria-label="Close edit blog"><i class="fas fa-times"></i></button>
                        </div>
                        <form class="blog-edit-form" method="POST" action="{{ route('user.blogs.update', $blog->id) }}" enctype="multipart/form-data">
                          @csrf
                          @method('PUT')
                          <img class="blog-edit-preview" src="{{ $blog->image_url }}" alt="{{ $blog->title }}">
                          <div class="blog-field">
                            <label for="edit-blog-title-{{ $blog->id }}">Blog title</label>
                            <input id="edit-blog-title-{{ $blog->id }}" type="text" name="title" value="{{ $blog->title }}" maxlength="200" required>
                          </div>
                          <div class="blog-field">
                            <label for="edit-blog-content-{{ $blog->id }}">Blog content</label>
                            <textarea id="edit-blog-content-{{ $blog->id }}" name="content" rows="10" maxlength="20000" required>{{ $blog->content }}</textarea>
                          </div>
                          <div class="blog-field">
                            <label for="edit-blog-image-{{ $blog->id }}">Replace image <span>(optional)</span></label>
                            <input class="blog-file-input" id="edit-blog-image-{{ $blog->id }}" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <span class="blog-file-hint">JPG, PNG or WebP · Max 5 MB</span>
                          </div>
                          <button class="blog-submit" type="submit">Save changes</button>
                        </form>
                      </dialog>
                      <form method="POST" action="{{ route('user.blogs.status', $blog->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="blog-status-button {{ $blog->is_active ? 'deactivate' : 'activate' }}" type="submit">{{ $blog->is_active ? 'Deactivate' : 'Activate' }}</button>
                      </form>
                      <form method="POST" action="{{ route('user.blogs.destroy', $blog->id) }}" onsubmit="return confirm('Delete this blog post permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="blog-delete-button" type="submit" aria-label="Delete {{ $blog->title }}"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    </div>
                  </div>
                  <p class="blog-card-copy">{{ $blog->content }}</p>
                </div>
              </article>
            @empty
              <div class="blog-empty">
                <span><i class="fas fa-blog"></i></span>
                <h3>No blog posts yet</h3>
                <p>Write and publish your first blog post with a featured image.</p>
              </div>
            @endforelse
          </div>
          @if ($blogs->hasPages())
            <div class="blog-pagination">{{ $blogs->links('vendor.pagination.compact') }}</div>
          @endif
        </section>
      </div>
    </div>
  </div>
</main>

<style>
  .blog-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .blog-layout { display: grid; grid-template-columns: minmax(290px, .78fr) minmax(0, 1.22fr); align-items: start; gap: 20px; }
  .blog-form-panel, .blog-list-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .blog-form { display: grid; gap: 13px; padding: 0 20px 21px; }
  .blog-field { min-width: 0; display: grid; gap: 7px; }
  .blog-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .blog-field label span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .blog-field input:not([type=file]), .blog-field textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .blog-field input:focus, .blog-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .blog-field textarea { resize: vertical; line-height: 1.6; }
  .blog-upload-area { min-height: 132px; display: grid; justify-items: center; align-content: center; gap: 6px; padding: 14px; color: #536658; background: #f8f9f5; border: 1px dashed #cdd8c9; border-radius: 10px; cursor: pointer; text-align: center; }
  .blog-upload-area:hover { background: #f0f4ed; border-color: #93aa93; }
  .blog-upload-area input { width: min(100%, 230px); margin-top: 3px; color: #718071; font-size: 10px; }
  .blog-upload-icon { width: 34px; height: 34px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .blog-upload-area strong { font-size: 11px; }
  .blog-upload-area > span:last-of-type, .blog-file-name, .blog-file-hint { color: var(--muted); font-size: 10px; }
  .blog-file-name { min-height: 12px; margin: -8px 0 0; }
  .blog-error { color: #a34d40; font-size: 11px; }
  .blog-submit { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .blog-submit:hover { background: var(--green); }
  .blog-list-panel { display: flex; flex-direction: column; }
  .blog-filter { display: flex; align-items: center; gap: 8px; padding: 0 20px 14px; }
  .blog-search-field { min-width: 0; min-height: 37px; display: flex; flex: 1; align-items: center; gap: 8px; padding: 0 11px; color: #849084; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; }
  .blog-search-field input { width: 100%; min-width: 0; padding: 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 11px; }
  .blog-filter-status select { min-height: 37px; max-width: 130px; padding: 0 8px; color: #59665b; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 10px; }
  .blog-filter-submit, .blog-filter-reset { min-height: 37px; display: inline-flex; align-items: center; justify-content: center; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .blog-filter-reset { color: #68756a; background: #f1f3ed; }
  .blog-list-scroll { max-height: min(68vh, 720px); display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-content: start; gap: 12px; overflow-y: auto; overscroll-behavior: contain; padding: 0 16px 16px; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; }
  .blog-card { min-width: 0; overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
  .blog-image-wrap { position: relative; height: 155px; overflow: hidden; background: #f0f2ec; }
  .blog-image-wrap img { width: 100%; height: 100%; display: block; object-fit: cover; }
  .blog-card-status { position: absolute; top: 9px; left: 9px; padding: 5px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
  .blog-card-status.active { color: #365844; background: #e8f1e6; }
  .blog-card-status.inactive { color: #8b6557; background: #f5ece7; }
  .blog-card-content { padding: 11px; }
  .blog-card-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 7px; }
  .blog-card-heading h3 { margin: 0 0 4px; font-size: 11px; line-height: 1.45; overflow-wrap: anywhere; }
  .blog-card-heading > div:first-child > span { color: var(--muted); font-size: 9px; }
  .blog-actions { display: flex; align-items: center; flex-wrap: wrap; justify-content: flex-end; gap: 5px; }
  .blog-actions form { margin: 0; }
  .blog-edit-button, .blog-delete-button { width: 27px; height: 27px; display: grid; place-items: center; border-radius: 6px; cursor: pointer; font-size: 10px; }
  .blog-edit-button { color: #526255; background: #f1f3ed; border: 0; }
  .blog-status-button { min-height: 27px; padding: 0 7px; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font-size: 9px; font-weight: 700; white-space: nowrap; }
  .blog-status-button.deactivate { color: #8b6557; background: #fff; border-color: #ead9d0; }
  .blog-status-button.activate { color: #365844; background: #fff; border-color: #d5e6d2; }
  .blog-delete-button { color: #a34d40; background: #fff; border: 1px solid #ead9d0; }
  .blog-delete-button:hover { color: #fff; background: #a34d40; border-color: #a34d40; }
  .blog-card-copy { max-height: 110px; margin: 9px 0 0; overflow: hidden; color: #647064; font-size: 10px; line-height: 1.6; white-space: pre-line; overflow-wrap: anywhere; }
  .blog-edit-dialog { width: min(560px, calc(100vw - 32px)); max-height: min(88vh, 780px); padding: 0; overflow-y: auto; color: var(--ink); background: #fffefa; border: 1px solid var(--line); border-radius: 13px; box-shadow: 0 24px 70px rgba(34,48,38,.24); }
  .blog-edit-dialog::backdrop { background: rgba(28,40,32,.42); backdrop-filter: blur(2px); }
  .blog-edit-heading { position: sticky; z-index: 1; top: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 17px 20px; background: #fffefa; border-bottom: 1px solid var(--line); }
  .blog-edit-heading h2 { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
  .blog-edit-heading p { margin: 0; color: var(--muted); font-size: 11px; }
  .blog-edit-close { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #647064; background: #f1f3ed; border: 0; border-radius: 50%; cursor: pointer; }
  .blog-edit-form { display: grid; gap: 15px; padding: 18px 20px 20px; }
  .blog-edit-preview { width: 100%; max-height: 190px; object-fit: cover; border-radius: 9px; }
  .blog-file-input { width: 100%; padding: 9px; border: 1px solid #e2e5dc; border-radius: 8px; font: inherit; font-size: 11px; }
  .blog-empty { grid-column: 1 / -1; display: grid; justify-items: center; padding: 46px 20px; text-align: center; }
  .blog-empty > span { width: 46px; height: 46px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .blog-empty h3 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .blog-empty p { margin: 0; color: var(--muted); font-size: 12px; }
  .blog-pagination { display: flex; justify-content: center; padding: 12px 20px 18px; border-top: 1px solid var(--line); }
  .blog-pagination .pagination-links { display: flex; align-items: center; gap: 5px; }
  .blog-pagination .pagination-links a, .blog-pagination .pagination-links > span { min-width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: #68756a; font-size: 11px; }
  .blog-pagination .pagination-links [aria-current=page] { color: #fff; background: var(--green); border-color: var(--green); }
  .blog-pagination svg { width: 15px; height: 15px; }
  @media (max-width: 850px) { .blog-layout { grid-template-columns: 1fr; } }
  @media (max-width: 560px) { .blog-list-scroll { grid-template-columns: 1fr; } .blog-filter { flex-wrap: wrap; } .blog-search-field { flex-basis: 100%; } .blog-image-wrap { height: 190px; } }
</style>
<script>
  document.querySelectorAll('[data-open-blog-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.openBlogEdit);
      if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-blog-edit]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
  const blogImageInput = document.querySelector('#blog-image');
  const blogFileName = document.querySelector('[data-blog-file-name]');
  if (blogImageInput && blogFileName) {
    blogImageInput.addEventListener('change', () => {
      blogFileName.textContent = blogImageInput.files?.[0]?.name || 'No image selected';
    });
  }
</script>
@endsection
