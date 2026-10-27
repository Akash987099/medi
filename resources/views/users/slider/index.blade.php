@extends('users.layout.app')

@section('title', 'Slider')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Slider</div>
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
      <section class="page-heading" aria-labelledby="slider-title">
        <div>
          <p class="eyebrow">Your visual first impression</p>
          <h1 id="slider-title">Slider</h1>
          <p class="page-subtitle">Add and arrange image slides for your workspace. Lower display order numbers appear first.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="slider-alert" role="status">{{ session('status') }}</div>
      @endif

      <div class="slider-layout">
        <section class="panel slider-form-panel" aria-labelledby="add-slide-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="add-slide-title">Add a slide</h2>
              <p class="panel-caption">Upload a banner image with a title and optional caption.</p>
            </div>
          </div>
          <form class="slider-form" method="POST" action="{{ route('user.slider.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="slider-field">
              <label for="slide-title">Slide title</label>
              <input id="slide-title" type="text" name="title" value="{{ old('title') }}" maxlength="160" placeholder="e.g. Compassionate care, close to home" required>
              @error('title')<span class="slider-error">{{ $message }}</span>@enderror
            </div>
            <div class="slider-field">
              <label for="slide-caption">Caption <span>(optional)</span></label>
              <textarea id="slide-caption" name="caption" rows="3" maxlength="1000" placeholder="Add a short supporting message...">{{ old('caption') }}</textarea>
              @error('caption')<span class="slider-error">{{ $message }}</span>@enderror
            </div>
            <div class="slider-field">
              <label for="slide-order">Display order</label>
              <input id="slide-order" type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="9999" required>
              @error('sort_order')<span class="slider-error">{{ $message }}</span>@enderror
            </div>
            <label class="slider-upload-area" for="slide-image">
              <span class="slider-upload-icon"><i class="far fa-image"></i></span>
              <strong>Choose a slider image</strong>
              <span>JPG, PNG or WebP · Max 5 MB</span>
              <input id="slide-image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" required>
            </label>
            @error('image')<span class="slider-error">{{ $message }}</span>@enderror
            <p class="slider-file-name" data-slider-file-name>No image selected</p>
            <button class="slider-submit" type="submit"><i class="fas fa-plus"></i> Add slide</button>
          </form>
        </section>

        <section class="panel slider-list-panel" aria-labelledby="saved-slides-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="saved-slides-title">Saved slides</h2>
              <p class="panel-caption">{{ $slides->total() }} {{ \Illuminate\Support\Str::plural('slide', $slides->total()) }} in your workspace</p>
            </div>
          </div>
          <form class="slider-filter" method="GET" action="{{ route('user.slider.index') }}">
            <label class="slider-search-field" for="slider-search">
              <i class="fas fa-search" aria-hidden="true"></i>
              <input id="slider-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Find by slide title or caption">
            </label>
            <label class="slider-filter-status" for="slider-status-filter">
              <span class="sr-only">Filter by status</span>
              <select id="slider-status-filter" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
              </select>
            </label>
            <button class="slider-filter-submit" type="submit">Find</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
              <a class="slider-filter-reset" href="{{ route('user.slider.index') }}">Clear</a>
            @endif
          </form>
          <div class="slider-list-scroll">
            @forelse ($slides as $slide)
              <article class="slider-card">
                <div class="slider-image-wrap">
                  <img src="{{ $slide->image_url }}" alt="{{ $slide->title }}" loading="lazy">
                  <span class="slider-order-badge">Order {{ $slide->sort_order }}</span>
                  <span class="slider-card-status {{ $slide->is_active ? 'active' : 'inactive' }}">{{ $slide->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="slider-card-content">
                  <div class="slider-card-heading">
                    <div><h3>{{ $slide->title }}</h3><span>{{ $slide->created_at->format('M j, Y') }}</span></div>
                    <div class="slider-actions">
                      <button class="slider-edit-button" type="button" data-open-slider-edit="slider-edit-dialog-{{ $slide->id }}" aria-label="Edit {{ $slide->title }}"><i class="fas fa-pen"></i></button>
                      <dialog class="slider-edit-dialog" id="slider-edit-dialog-{{ $slide->id }}" aria-labelledby="slider-edit-title-{{ $slide->id }}">
                        <div class="slider-edit-heading">
                          <div><h2 id="slider-edit-title-{{ $slide->id }}">Edit slider slide</h2><p>Update its text, display order or image.</p></div>
                          <button class="slider-edit-close" type="button" data-close-slider-edit aria-label="Close edit slide"><i class="fas fa-times"></i></button>
                        </div>
                        <form class="slider-edit-form" method="POST" action="{{ route('user.slider.update', $slide->id) }}" enctype="multipart/form-data">
                          @csrf
                          @method('PUT')
                          <img class="slider-edit-preview" src="{{ $slide->image_url }}" alt="{{ $slide->title }}">
                          <div class="slider-field">
                            <label for="edit-slide-title-{{ $slide->id }}">Slide title</label>
                            <input id="edit-slide-title-{{ $slide->id }}" type="text" name="title" value="{{ $slide->title }}" maxlength="160" required>
                          </div>
                          <div class="slider-field">
                            <label for="edit-slide-caption-{{ $slide->id }}">Caption <span>(optional)</span></label>
                            <textarea id="edit-slide-caption-{{ $slide->id }}" name="caption" rows="4" maxlength="1000">{{ $slide->caption }}</textarea>
                          </div>
                          <div class="slider-field">
                            <label for="edit-slide-order-{{ $slide->id }}">Display order</label>
                            <input id="edit-slide-order-{{ $slide->id }}" type="number" name="sort_order" value="{{ $slide->sort_order }}" min="0" max="9999" required>
                          </div>
                          <div class="slider-field">
                            <label for="edit-slide-image-{{ $slide->id }}">Replace image <span>(optional)</span></label>
                            <input class="slider-file-input" id="edit-slide-image-{{ $slide->id }}" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <span class="slider-file-hint">JPG, PNG or WebP · Max 5 MB</span>
                          </div>
                          <button class="slider-submit" type="submit">Save changes</button>
                        </form>
                      </dialog>
                      <form method="POST" action="{{ route('user.slider.status', $slide->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="slider-status-button {{ $slide->is_active ? 'deactivate' : 'activate' }}" type="submit">{{ $slide->is_active ? 'Deactivate' : 'Activate' }}</button>
                      </form>
                      <form method="POST" action="{{ route('user.slider.destroy', $slide->id) }}" onsubmit="return confirm('Delete this slider slide permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="slider-delete-button" type="submit" aria-label="Delete {{ $slide->title }}"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    </div>
                  </div>
                  @if ($slide->caption)
                    <p class="slider-caption">{{ $slide->caption }}</p>
                  @endif
                </div>
              </article>
            @empty
              <div class="slider-empty">
                <span><i class="far fa-images"></i></span>
                <h3>No slides yet</h3>
                <p>Add an image slide to build your workspace slider.</p>
              </div>
            @endforelse
          </div>
          @if ($slides->hasPages())
            <div class="slider-pagination">{{ $slides->links('vendor.pagination.compact') }}</div>
          @endif
        </section>
      </div>
    </div>
  </div>
</main>

<style>
  .slider-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .slider-layout { display: grid; grid-template-columns: minmax(290px, .78fr) minmax(0, 1.22fr); align-items: start; gap: 20px; }
  .slider-form-panel, .slider-list-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .slider-form { display: grid; gap: 13px; padding: 0 20px 21px; }
  .slider-field { min-width: 0; display: grid; gap: 7px; }
  .slider-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .slider-field label span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .slider-field input:not([type=file]), .slider-field textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .slider-field input:focus, .slider-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .slider-field textarea { resize: vertical; line-height: 1.6; }
  .slider-upload-area { min-height: 132px; display: grid; justify-items: center; align-content: center; gap: 6px; padding: 14px; color: #536658; background: #f8f9f5; border: 1px dashed #cdd8c9; border-radius: 10px; cursor: pointer; text-align: center; }
  .slider-upload-area:hover { background: #f0f4ed; border-color: #93aa93; }
  .slider-upload-area input { width: min(100%, 230px); margin-top: 3px; color: #718071; font-size: 10px; }
  .slider-upload-icon { width: 34px; height: 34px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .slider-upload-area strong { font-size: 11px; }
  .slider-upload-area > span:last-of-type, .slider-file-name, .slider-file-hint { color: var(--muted); font-size: 10px; }
  .slider-file-name { min-height: 12px; margin: -8px 0 0; }
  .slider-error { color: #a34d40; font-size: 11px; }
  .slider-submit { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .slider-submit:hover { background: var(--green); }
  .slider-list-panel { display: flex; flex-direction: column; }
  .slider-filter { display: flex; align-items: center; gap: 8px; padding: 0 20px 14px; }
  .slider-search-field { min-width: 0; min-height: 37px; display: flex; flex: 1; align-items: center; gap: 8px; padding: 0 11px; color: #849084; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; }
  .slider-search-field input { width: 100%; min-width: 0; padding: 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 11px; }
  .slider-filter-status select { min-height: 37px; max-width: 130px; padding: 0 8px; color: #59665b; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 10px; }
  .slider-filter-submit, .slider-filter-reset { min-height: 37px; display: inline-flex; align-items: center; justify-content: center; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .slider-filter-reset { color: #68756a; background: #f1f3ed; }
  .slider-list-scroll { max-height: min(68vh, 720px); display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-content: start; gap: 12px; overflow-y: auto; overscroll-behavior: contain; padding: 0 16px 16px; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; }
  .slider-card { min-width: 0; overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
  .slider-image-wrap { position: relative; height: 155px; overflow: hidden; background: #f0f2ec; }
  .slider-image-wrap img { width: 100%; height: 100%; display: block; object-fit: cover; }
  .slider-order-badge, .slider-card-status { position: absolute; top: 9px; padding: 5px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
  .slider-order-badge { left: 9px; color: #526255; background: #fff; }
  .slider-card-status { right: 9px; }
  .slider-card-status.active { color: #365844; background: #e8f1e6; }
  .slider-card-status.inactive { color: #8b6557; background: #f5ece7; }
  .slider-card-content { padding: 11px; }
  .slider-card-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 7px; }
  .slider-card-heading h3 { margin: 0 0 4px; font-size: 11px; line-height: 1.45; overflow-wrap: anywhere; }
  .slider-card-heading > div:first-child > span { color: var(--muted); font-size: 9px; }
  .slider-actions { display: flex; align-items: center; flex-wrap: wrap; justify-content: flex-end; gap: 5px; }
  .slider-actions form { margin: 0; }
  .slider-edit-button, .slider-delete-button { width: 27px; height: 27px; display: grid; place-items: center; border-radius: 6px; cursor: pointer; font-size: 10px; }
  .slider-edit-button { color: #526255; background: #f1f3ed; border: 0; }
  .slider-status-button { min-height: 27px; padding: 0 7px; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font-size: 9px; font-weight: 700; white-space: nowrap; }
  .slider-status-button.deactivate { color: #8b6557; background: #fff; border-color: #ead9d0; }
  .slider-status-button.activate { color: #365844; background: #fff; border-color: #d5e6d2; }
  .slider-delete-button { color: #a34d40; background: #fff; border: 1px solid #ead9d0; }
  .slider-delete-button:hover { color: #fff; background: #a34d40; border-color: #a34d40; }
  .slider-caption { margin: 9px 0 0; color: #647064; font-size: 10px; line-height: 1.55; white-space: pre-line; overflow-wrap: anywhere; }
  .slider-edit-dialog { width: min(480px, calc(100vw - 32px)); max-height: min(88vh, 780px); padding: 0; overflow-y: auto; color: var(--ink); background: #fffefa; border: 1px solid var(--line); border-radius: 13px; box-shadow: 0 24px 70px rgba(34,48,38,.24); }
  .slider-edit-dialog::backdrop { background: rgba(28,40,32,.42); backdrop-filter: blur(2px); }
  .slider-edit-heading { position: sticky; z-index: 1; top: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 17px 20px; background: #fffefa; border-bottom: 1px solid var(--line); }
  .slider-edit-heading h2 { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
  .slider-edit-heading p { margin: 0; color: var(--muted); font-size: 11px; }
  .slider-edit-close { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #647064; background: #f1f3ed; border: 0; border-radius: 50%; cursor: pointer; }
  .slider-edit-form { display: grid; gap: 15px; padding: 18px 20px 20px; }
  .slider-edit-preview { width: 100%; max-height: 190px; object-fit: cover; border-radius: 9px; }
  .slider-file-input { width: 100%; padding: 9px; border: 1px solid #e2e5dc; border-radius: 8px; font: inherit; font-size: 11px; }
  .slider-empty { grid-column: 1 / -1; display: grid; justify-items: center; padding: 46px 20px; text-align: center; }
  .slider-empty > span { width: 46px; height: 46px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .slider-empty h3 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .slider-empty p { margin: 0; color: var(--muted); font-size: 12px; }
  .slider-pagination { display: flex; justify-content: center; padding: 12px 20px 18px; border-top: 1px solid var(--line); }
  .slider-pagination .pagination-links { display: flex; align-items: center; gap: 5px; }
  .slider-pagination .pagination-links a, .slider-pagination .pagination-links > span { min-width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: #68756a; font-size: 11px; }
  .slider-pagination .pagination-links [aria-current=page] { color: #fff; background: var(--green); border-color: var(--green); }
  .slider-pagination svg { width: 15px; height: 15px; }
  @media (max-width: 850px) { .slider-layout { grid-template-columns: 1fr; } }
  @media (max-width: 560px) { .slider-list-scroll { grid-template-columns: 1fr; } .slider-filter { flex-wrap: wrap; } .slider-search-field { flex-basis: 100%; } .slider-image-wrap { height: 190px; } }
</style>
<script>
  document.querySelectorAll('[data-open-slider-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.openSliderEdit);
      if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-slider-edit]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
  const sliderImageInput = document.querySelector('#slide-image');
  const sliderFileName = document.querySelector('[data-slider-file-name]');
  if (sliderImageInput && sliderFileName) {
    sliderImageInput.addEventListener('change', () => {
      sliderFileName.textContent = sliderImageInput.files?.[0]?.name || 'No image selected';
    });
  }
</script>
@endsection
