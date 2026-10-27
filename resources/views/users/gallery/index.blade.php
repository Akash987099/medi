@extends('users.layout.app')

@section('title', 'Gallery')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Gallery</div>
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
      <section class="page-heading" aria-labelledby="gallery-title">
        <div>
          <p class="eyebrow">Your moments of care</p>
          <h1 id="gallery-title">Gallery</h1>
          <p class="page-subtitle">Share photos of your clinic, facilities and care team.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="gallery-alert" role="status">{{ session('status') }}</div>
      @endif

      <div class="gallery-layout">
        <section class="panel gallery-upload-panel" aria-labelledby="upload-image-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="upload-image-title">Add to gallery</h2>
              <p class="panel-caption">Upload a photo with a title and optional caption.</p>
            </div>
          </div>
          <form class="gallery-form" method="POST" action="{{ route('user.gallery.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="gallery-field">
              <label for="title">Image title</label>
              <input id="title" type="text" name="title" value="{{ old('title') }}" maxlength="120" placeholder="e.g. Reception area" required>
              @error('title')<span class="gallery-error">{{ $message }}</span>@enderror
            </div>
            <div class="gallery-field">
              <label for="caption">Caption <span>(optional)</span></label>
              <textarea id="caption" name="caption" rows="3" maxlength="1000" placeholder="Add a short description...">{{ old('caption') }}</textarea>
              @error('caption')<span class="gallery-error">{{ $message }}</span>@enderror
            </div>
            <label class="gallery-upload-area" for="image">
              <span class="gallery-upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
              <strong>Choose an image</strong>
              <span>JPG, PNG or WebP · Max 5 MB</span>
              <input id="image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
            </label>
            @error('image')<span class="gallery-error">{{ $message }}</span>@enderror
            <p class="gallery-file-name" data-upload-file-name>No image selected</p>
            <button class="gallery-submit" type="submit"><i class="fas fa-plus"></i> Upload image</button>
          </form>
        </section>

        <section class="panel gallery-list-panel" aria-labelledby="saved-images-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="saved-images-title">Saved images</h2>
              <p class="panel-caption">{{ $images->total() }} {{ \Illuminate\Support\Str::plural('image', $images->total()) }} in your workspace</p>
            </div>
          </div>
          <form class="gallery-filter" method="GET" action="{{ route('user.gallery.index') }}">
            <label class="gallery-search-field" for="gallery-search">
              <i class="fas fa-search" aria-hidden="true"></i>
              <input id="gallery-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Find by image title or caption">
            </label>
            <label class="gallery-filter-status" for="gallery-status-filter">
              <span class="sr-only">Filter by status</span>
              <select id="gallery-status-filter" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
              </select>
            </label>
            <button class="gallery-filter-submit" type="submit">Find</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
              <a class="gallery-filter-reset" href="{{ route('user.gallery.index') }}">Clear</a>
            @endif
          </form>
          <div class="gallery-list-scroll">
            @forelse ($images as $image)
              <article class="gallery-card">
                <div class="gallery-image-wrap">
                  <img src="{{ $image->image_url }}" alt="{{ $image->title }}" loading="lazy">
                  <span class="gallery-card-status {{ $image->is_active ? 'active' : 'inactive' }}">{{ $image->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="gallery-card-content">
                  <div class="gallery-card-heading">
                    <div><h3>{{ $image->title }}</h3><span>{{ $image->created_at->format('M j, Y') }}</span></div>
                    <div class="gallery-actions">
                      <button class="gallery-edit-button" type="button" data-open-gallery-edit="gallery-edit-dialog-{{ $image->id }}" aria-label="Edit {{ $image->title }}"><i class="fas fa-pen"></i></button>
                      <dialog class="gallery-edit-dialog" id="gallery-edit-dialog-{{ $image->id }}" aria-labelledby="gallery-edit-title-{{ $image->id }}">
                        <div class="gallery-edit-heading">
                          <div><h2 id="gallery-edit-title-{{ $image->id }}">Edit gallery image</h2><p>Update its title, caption or photo.</p></div>
                          <button class="gallery-edit-close" type="button" data-close-gallery-edit aria-label="Close edit gallery image"><i class="fas fa-times"></i></button>
                        </div>
                        <form class="gallery-edit-form" method="POST" action="{{ route('user.gallery.update', $image->id) }}" enctype="multipart/form-data">
                          @csrf
                          @method('PUT')
                          <img class="gallery-edit-preview" src="{{ $image->image_url }}" alt="{{ $image->title }}">
                          <div class="gallery-field">
                            <label for="edit-title-{{ $image->id }}">Image title</label>
                            <input id="edit-title-{{ $image->id }}" type="text" name="title" value="{{ $image->title }}" maxlength="120" required>
                          </div>
                          <div class="gallery-field">
                            <label for="edit-caption-{{ $image->id }}">Caption <span>(optional)</span></label>
                            <textarea id="edit-caption-{{ $image->id }}" name="caption" rows="4" maxlength="1000">{{ $image->caption }}</textarea>
                          </div>
                          <div class="gallery-field">
                            <label for="edit-image-{{ $image->id }}">Replace image <span>(optional)</span></label>
                            <input class="gallery-file-input" id="edit-image-{{ $image->id }}" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <span class="gallery-file-hint">JPG, PNG or WebP · Max 5 MB</span>
                          </div>
                          <button class="gallery-submit" type="submit">Save changes</button>
                        </form>
                      </dialog>
                      <form method="POST" action="{{ route('user.gallery.status', $image->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="gallery-status-button {{ $image->is_active ? 'deactivate' : 'activate' }}" type="submit">{{ $image->is_active ? 'Deactivate' : 'Activate' }}</button>
                      </form>
                      <form method="POST" action="{{ route('user.gallery.destroy', $image->id) }}" onsubmit="return confirm('Delete this gallery image permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="gallery-delete-button" type="submit" aria-label="Delete {{ $image->title }}"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    </div>
                  </div>
                  @if ($image->caption)
                    <p class="gallery-caption">{{ $image->caption }}</p>
                  @endif
                </div>
              </article>
            @empty
              <div class="gallery-empty">
                <span><i class="far fa-images"></i></span>
                <h3>No images yet</h3>
                <p>Upload a photo to start building your gallery.</p>
              </div>
            @endforelse
          </div>
          @if ($images->hasPages())
            <div class="gallery-pagination">{{ $images->links('vendor.pagination.compact') }}</div>
          @endif
        </section>
      </div>
    </div>
  </div>
</main>

<style>
  .gallery-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .gallery-layout { display: grid; grid-template-columns: minmax(290px, .78fr) minmax(0, 1.22fr); align-items: start; gap: 20px; }
  .gallery-upload-panel, .gallery-list-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .gallery-form { display: grid; gap: 13px; padding: 0 20px 21px; }
  .gallery-field { min-width: 0; display: grid; gap: 7px; }
  .gallery-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .gallery-field label span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .gallery-field input:not([type=file]), .gallery-field textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .gallery-field input:focus, .gallery-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .gallery-field textarea { resize: vertical; line-height: 1.6; }
  .gallery-upload-area { min-height: 132px; display: grid; justify-items: center; align-content: center; gap: 6px; padding: 14px; color: #536658; background: #f8f9f5; border: 1px dashed #cdd8c9; border-radius: 10px; cursor: pointer; text-align: center; }
  .gallery-upload-area:hover { background: #f0f4ed; border-color: #93aa93; }
  .gallery-upload-area input { width: min(100%, 230px); margin-top: 3px; color: #718071; font-size: 10px; }
  .gallery-upload-icon { width: 34px; height: 34px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .gallery-upload-area strong { font-size: 11px; }
  .gallery-upload-area > span:last-of-type, .gallery-file-name, .gallery-file-hint { color: var(--muted); font-size: 10px; }
  .gallery-file-name { min-height: 12px; margin: -8px 0 0; }
  .gallery-error { color: #a34d40; font-size: 11px; }
  .gallery-submit { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .gallery-submit:hover { background: var(--green); }
  .gallery-list-panel { display: flex; flex-direction: column; }
  .gallery-filter { display: flex; align-items: center; gap: 8px; padding: 0 20px 14px; }
  .gallery-search-field { min-width: 0; min-height: 37px; display: flex; flex: 1; align-items: center; gap: 8px; padding: 0 11px; color: #849084; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; }
  .gallery-search-field input { width: 100%; min-width: 0; padding: 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 11px; }
  .gallery-filter-status select { min-height: 37px; max-width: 130px; padding: 0 8px; color: #59665b; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 10px; }
  .gallery-filter-submit, .gallery-filter-reset { min-height: 37px; display: inline-flex; align-items: center; justify-content: center; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .gallery-filter-reset { color: #68756a; background: #f1f3ed; }
  .gallery-list-scroll { max-height: min(68vh, 720px); display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-content: start; gap: 12px; overflow-y: auto; overscroll-behavior: contain; padding: 0 16px 16px; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; }
  .gallery-card { min-width: 0; overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
  .gallery-image-wrap { position: relative; height: 155px; overflow: hidden; background: #f0f2ec; }
  .gallery-image-wrap img { width: 100%; height: 100%; display: block; object-fit: cover; }
  .gallery-card-status { position: absolute; top: 9px; left: 9px; padding: 5px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
  .gallery-card-status.active { color: #365844; background: #e8f1e6; }
  .gallery-card-status.inactive { color: #8b6557; background: #f5ece7; }
  .gallery-card-content { padding: 11px; }
  .gallery-card-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 7px; }
  .gallery-card-heading h3 { margin: 0 0 4px; font-size: 11px; line-height: 1.45; overflow-wrap: anywhere; }
  .gallery-card-heading > div:first-child > span { color: var(--muted); font-size: 9px; }
  .gallery-actions { display: flex; align-items: center; flex-wrap: wrap; justify-content: flex-end; gap: 5px; }
  .gallery-actions form { margin: 0; }
  .gallery-edit-button { width: 27px; height: 27px; display: grid; place-items: center; color: #526255; background: #f1f3ed; border: 0; border-radius: 6px; cursor: pointer; font-size: 10px; }
  .gallery-status-button { min-height: 27px; padding: 0 7px; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font-size: 9px; font-weight: 700; white-space: nowrap; }
  .gallery-status-button.deactivate { color: #8b6557; background: #fff; border-color: #ead9d0; }
  .gallery-status-button.activate { color: #365844; background: #fff; border-color: #d5e6d2; }
  .gallery-delete-button { width: 27px; height: 27px; display: grid; place-items: center; color: #a34d40; background: #fff; border: 1px solid #ead9d0; border-radius: 6px; cursor: pointer; font-size: 10px; }
  .gallery-delete-button:hover { color: #fff; background: #a34d40; border-color: #a34d40; }
  .gallery-caption { margin: 9px 0 0; color: #647064; font-size: 10px; line-height: 1.55; white-space: pre-line; overflow-wrap: anywhere; }
  .gallery-edit-dialog { width: min(480px, calc(100vw - 32px)); max-height: min(88vh, 780px); padding: 0; overflow-y: auto; color: var(--ink); background: #fffefa; border: 1px solid var(--line); border-radius: 13px; box-shadow: 0 24px 70px rgba(34,48,38,.24); }
  .gallery-edit-dialog::backdrop { background: rgba(28,40,32,.42); backdrop-filter: blur(2px); }
  .gallery-edit-heading { position: sticky; z-index: 1; top: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 17px 20px; background: #fffefa; border-bottom: 1px solid var(--line); }
  .gallery-edit-heading h2 { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
  .gallery-edit-heading p { margin: 0; color: var(--muted); font-size: 11px; }
  .gallery-edit-close { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #647064; background: #f1f3ed; border: 0; border-radius: 50%; cursor: pointer; }
  .gallery-edit-form { display: grid; gap: 15px; padding: 18px 20px 20px; }
  .gallery-edit-preview { width: 100%; max-height: 190px; object-fit: cover; border-radius: 9px; }
  .gallery-file-input { width: 100%; padding: 9px; border: 1px solid #e2e5dc; border-radius: 8px; font: inherit; font-size: 11px; }
  .gallery-empty { grid-column: 1 / -1; display: grid; justify-items: center; padding: 46px 20px; text-align: center; }
  .gallery-empty > span { width: 46px; height: 46px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .gallery-empty h3 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .gallery-empty p { margin: 0; color: var(--muted); font-size: 12px; }
  .gallery-pagination { display: flex; justify-content: center; padding: 12px 20px 18px; border-top: 1px solid var(--line); }
  .gallery-pagination .pagination-links { display: flex; align-items: center; gap: 5px; }
  .gallery-pagination .pagination-links a, .gallery-pagination .pagination-links > span { min-width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: #68756a; font-size: 11px; }
  .gallery-pagination .pagination-links [aria-current=page] { color: #fff; background: var(--green); border-color: var(--green); }
  .gallery-pagination svg { width: 15px; height: 15px; }
  @media (max-width: 850px) { .gallery-layout { grid-template-columns: 1fr; } }
  @media (max-width: 560px) { .gallery-list-scroll { grid-template-columns: 1fr; } .gallery-filter { flex-wrap: wrap; } .gallery-search-field { flex-basis: 100%; } .gallery-image-wrap { height: 190px; } }
</style>
<script>
  document.querySelectorAll('[data-open-gallery-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.openGalleryEdit);
      if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-gallery-edit]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
  const galleryImageInput = document.querySelector('#image');
  const galleryFileName = document.querySelector('[data-upload-file-name]');
  if (galleryImageInput && galleryFileName) {
    galleryImageInput.addEventListener('change', () => {
      galleryFileName.textContent = galleryImageInput.files?.[0]?.name || 'No image selected';
    });
  }
</script>
@endsection
