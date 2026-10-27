@extends('users.layout.app')

@section('title', 'Videos')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Videos</div>
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
      <section class="page-heading" aria-labelledby="videos-title">
        <div>
          <p class="eyebrow">Video library</p>
          <h1 id="videos-title">Videos</h1>
          <p class="page-subtitle">Upload a video or add a YouTube video link. Each entry uses one source only.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="video-alert" role="status">{{ session('status') }}</div>
      @endif

      <div class="video-layout">
        <section class="panel video-form-panel" aria-labelledby="add-video-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="add-video-title">Add a video</h2>
              <p class="panel-caption">Choose either a video upload or a YouTube iframe link.</p>
            </div>
          </div>
          <form class="video-form" method="POST" action="{{ route('user.videos.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="video-field">
              <label for="title">Video title</label>
              <input id="title" type="text" name="title" value="{{ old('title') }}" maxlength="120" placeholder="e.g. Meet our care team" required>
              @error('title')<span class="video-error">{{ $message }}</span>@enderror
            </div>
            <div class="video-field">
              <label for="caption">Caption <span>(optional)</span></label>
              <textarea id="caption" name="caption" rows="3" maxlength="1000" placeholder="Add a short description...">{{ old('caption') }}</textarea>
              @error('caption')<span class="video-error">{{ $message }}</span>@enderror
            </div>

            <fieldset class="video-source-field">
              <legend>Video source</legend>
              <div class="video-source-options">
                <label class="video-source-option">
                  <input type="radio" name="video_type" value="upload" @checked(old('video_type', 'upload') === 'upload') required>
                  <span><i class="fas fa-upload"></i><strong>Upload video</strong><small>MP4, WebM or OGG · Max 50 MB</small></span>
                </label>
                <label class="video-source-option">
                  <input type="radio" name="video_type" value="youtube" @checked(old('video_type') === 'youtube') required>
                  <span><i class="fab fa-youtube"></i><strong>YouTube iframe</strong><small>Paste a YouTube video URL</small></span>
                </label>
              </div>
              @error('video_type')<span class="video-error">{{ $message }}</span>@enderror
            </fieldset>

            <div class="video-source-input" data-video-source="upload">
              <label class="video-upload-area" for="video">
                <span class="video-upload-icon"><i class="fas fa-cloud-upload-alt"></i></span>
                <strong>Choose a video file</strong>
                <span>MP4, WebM or OGG · Max 50 MB</span>
                <input id="video" type="file" name="video" accept="video/mp4,video/webm,video/ogg,.mp4,.webm,.ogv">
              </label>
              @error('video')<span class="video-error">{{ $message }}</span>@enderror
              <p class="video-file-name" data-video-file-name>No video selected</p>
            </div>

            <div class="video-source-input" data-video-source="youtube" hidden>
              <label class="video-field" for="youtube_url">YouTube video URL
                <input id="youtube_url" type="url" name="youtube_url" value="{{ old('youtube_url') }}" maxlength="2048" placeholder="https://www.youtube.com/watch?v=..." autocomplete="url">
              </label>
              <span class="video-help">Use a YouTube video link. The page creates a safe embedded player; iframe HTML is not accepted.</span>
              @error('youtube_url')<span class="video-error">{{ $message }}</span>@enderror
            </div>

            <button class="video-submit" type="submit"><i class="fas fa-plus"></i> Save video</button>
          </form>
        </section>

        <section class="panel video-list-panel" aria-labelledby="saved-videos-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="saved-videos-title">Saved videos</h2>
              <p class="panel-caption">{{ $videos->total() }} {{ \Illuminate\Support\Str::plural('video', $videos->total()) }} in your workspace</p>
            </div>
          </div>
          <form class="video-filter" method="GET" action="{{ route('user.videos.index') }}">
            <label class="video-search-field" for="video-search">
              <i class="fas fa-search" aria-hidden="true"></i>
              <input id="video-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Find by video title or caption">
            </label>
            <label class="video-filter-status" for="video-status-filter">
              <span class="sr-only">Filter by status</span>
              <select id="video-status-filter" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
              </select>
            </label>
            <button class="video-filter-submit" type="submit">Find</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
              <a class="video-filter-reset" href="{{ route('user.videos.index') }}">Clear</a>
            @endif
          </form>
          <div class="video-list-scroll">
            @forelse ($videos as $video)
              <article class="video-card">
                <div class="video-player-wrap">
                  @if ($video->video_type === 'upload')
                    <video controls preload="metadata" playsinline>
                      <source src="{{ $video->video_url }}">
                      Your browser does not support this video.
                    </video>
                  @else
                    <iframe src="{{ $video->youtube_embed_url }}" title="{{ $video->title }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                  @endif
                  <span class="video-card-status {{ $video->is_active ? 'active' : 'inactive' }}">{{ $video->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="video-card-content">
                  <div class="video-card-heading">
                    <div><h3>{{ $video->title }}</h3><span>{{ $video->video_type === 'upload' ? 'Uploaded video' : 'YouTube video' }} · {{ $video->created_at->format('M j, Y') }}</span></div>
                    <div class="video-actions">
                      <button class="video-edit-button" type="button" data-open-video-edit="video-edit-dialog-{{ $video->id }}" aria-label="Edit {{ $video->title }}"><i class="fas fa-pen"></i></button>
                      <dialog class="video-edit-dialog" id="video-edit-dialog-{{ $video->id }}" aria-labelledby="video-edit-title-{{ $video->id }}">
                        <div class="video-edit-heading">
                          <div><h2 id="video-edit-title-{{ $video->id }}">Edit video</h2><p>Choose one video source for this entry.</p></div>
                          <button class="video-edit-close" type="button" data-close-video-edit aria-label="Close edit video"><i class="fas fa-times"></i></button>
                        </div>
                        <form class="video-edit-form" method="POST" action="{{ route('user.videos.update', $video->id) }}" enctype="multipart/form-data">
                          @csrf
                          @method('PUT')
                          <div class="video-field">
                            <label for="edit-title-{{ $video->id }}">Video title</label>
                            <input id="edit-title-{{ $video->id }}" type="text" name="title" value="{{ $video->title }}" maxlength="120" required>
                          </div>
                          <div class="video-field">
                            <label for="edit-caption-{{ $video->id }}">Caption <span>(optional)</span></label>
                            <textarea id="edit-caption-{{ $video->id }}" name="caption" rows="3" maxlength="1000">{{ $video->caption }}</textarea>
                          </div>
                          <fieldset class="video-source-field">
                            <legend>Video source</legend>
                            <div class="video-source-options">
                              <label class="video-source-option">
                                <input type="radio" name="video_type" value="upload" @checked($video->video_type === 'upload') required>
                                <span><i class="fas fa-upload"></i><strong>Upload video</strong><small>Keep or replace uploaded video</small></span>
                              </label>
                              <label class="video-source-option">
                                <input type="radio" name="video_type" value="youtube" @checked($video->video_type === 'youtube') required>
                                <span><i class="fab fa-youtube"></i><strong>YouTube iframe</strong><small>Use one YouTube video URL</small></span>
                              </label>
                            </div>
                          </fieldset>
                          <div class="video-source-input" data-video-source="upload">
                            @if ($video->video_type === 'upload')
                              <video class="video-edit-preview" controls preload="metadata"><source src="{{ $video->video_url }}"></video>
                              <span class="video-help">Leave the file empty to keep the current upload.</span>
                            @endif
                            <div class="video-field">
                              <label for="edit-video-{{ $video->id }}">Upload or replace video</label>
                              <input class="video-file-input" id="edit-video-{{ $video->id }}" type="file" name="video" accept="video/mp4,video/webm,video/ogg,.mp4,.webm,.ogv">
                            </div>
                          </div>
                          <div class="video-source-input" data-video-source="youtube" hidden>
                            <div class="video-field">
                              <label for="edit-youtube-url-{{ $video->id }}">YouTube video URL</label>
                              <input id="edit-youtube-url-{{ $video->id }}" type="url" name="youtube_url" value="{{ $video->video_type === 'youtube' ? 'https://youtu.be/'.$video->youtube_video_id : '' }}" maxlength="2048" placeholder="https://www.youtube.com/watch?v=...">
                            </div>
                            <span class="video-help">Only a valid YouTube video URL is accepted; iframe HTML is not accepted.</span>
                          </div>
                          <button class="video-submit" type="submit">Save changes</button>
                        </form>
                      </dialog>
                      <form method="POST" action="{{ route('user.videos.status', $video->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="video-status-button {{ $video->is_active ? 'deactivate' : 'activate' }}" type="submit">{{ $video->is_active ? 'Deactivate' : 'Activate' }}</button>
                      </form>
                      <form method="POST" action="{{ route('user.videos.destroy', $video->id) }}" onsubmit="return confirm('Delete this video permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="video-delete-button" type="submit" aria-label="Delete {{ $video->title }}"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    </div>
                  </div>
                  @if ($video->caption)
                    <p class="video-caption">{{ $video->caption }}</p>
                  @endif
                </div>
              </article>
            @empty
              <div class="video-empty">
                <span><i class="fas fa-video"></i></span>
                <h3>No videos yet</h3>
                <p>Upload a video or add a YouTube link to get started.</p>
              </div>
            @endforelse
          </div>
          @if ($videos->hasPages())
            <div class="video-pagination">{{ $videos->links('vendor.pagination.compact') }}</div>
          @endif
        </section>
      </div>
    </div>
  </div>
</main>

<style>
  .video-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .video-layout { display: grid; grid-template-columns: minmax(300px, .78fr) minmax(0, 1.22fr); align-items: start; gap: 20px; }
  .video-form-panel, .video-list-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .video-form { display: grid; gap: 14px; padding: 0 20px 21px; }
  .video-field { min-width: 0; display: grid; gap: 7px; color: #59665b; font-size: 12px; font-weight: 700; }
  .video-field > span, .video-field > label > span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .video-field input:not([type=file]), .video-field textarea, .video-field input[type=url] { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; font-weight: 400; }
  .video-field input:focus, .video-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .video-field textarea { resize: vertical; line-height: 1.6; }
  .video-error { color: #a34d40; font-size: 11px; }
  .video-source-field { margin: 0; padding: 0; border: 0; }
  .video-source-field legend { margin-bottom: 8px; color: #59665b; font-size: 12px; font-weight: 700; }
  .video-source-options { display: grid; gap: 8px; }
  .video-source-option { position: relative; display: block; cursor: pointer; }
  .video-source-option input { position: absolute; top: 13px; left: 12px; z-index: 1; accent-color: #54745d; }
  .video-source-option > span { min-height: 58px; display: grid; grid-template-columns: 24px minmax(0, 1fr); align-content: center; column-gap: 8px; padding: 9px 10px 9px 37px; background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; }
  .video-source-option > span > i { grid-row: span 2; align-self: center; color: #54745d; font-size: 16px; }
  .video-source-option strong { color: #526255; font-size: 11px; }
  .video-source-option small { margin-top: 3px; color: var(--muted); font-size: 9px; }
  .video-source-option input:checked + span { background: #f1f5ef; border-color: #93aa93; box-shadow: 0 0 0 2px rgba(84,116,93,.08); }
  .video-source-input[hidden] { display: none !important; }
  .video-upload-area { min-height: 115px; display: grid; justify-items: center; align-content: center; gap: 6px; padding: 12px; color: #536658; background: #f8f9f5; border: 1px dashed #cdd8c9; border-radius: 10px; cursor: pointer; text-align: center; }
  .video-upload-area:hover { background: #f0f4ed; border-color: #93aa93; }
  .video-upload-area input { width: min(100%, 240px); margin-top: 3px; font-size: 10px; }
  .video-upload-area strong { font-size: 11px; }
  .video-upload-area > span:last-of-type, .video-file-name, .video-help { color: var(--muted); font-size: 10px; line-height: 1.5; }
  .video-upload-icon { width: 32px; height: 32px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .video-file-name { min-height: 12px; margin: -8px 0 0; }
  .video-submit { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .video-submit:hover { background: var(--green); }
  .video-list-panel { display: flex; flex-direction: column; }
  .video-filter { display: flex; align-items: center; gap: 8px; padding: 0 20px 14px; }
  .video-search-field { min-width: 0; min-height: 37px; display: flex; flex: 1; align-items: center; gap: 8px; padding: 0 11px; color: #849084; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; }
  .video-search-field input { width: 100%; min-width: 0; padding: 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 11px; }
  .video-filter-status select { min-height: 37px; max-width: 130px; padding: 0 8px; color: #59665b; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 10px; }
  .video-filter-submit, .video-filter-reset { min-height: 37px; display: inline-flex; align-items: center; justify-content: center; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .video-filter-reset { color: #68756a; background: #f1f3ed; }
  .video-list-scroll { max-height: min(68vh, 720px); display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-content: start; gap: 12px; overflow-y: auto; overscroll-behavior: contain; padding: 0 16px 16px; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; }
  .video-card { min-width: 0; overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 10px; }
  .video-player-wrap { position: relative; aspect-ratio: 16 / 9; overflow: hidden; background: #202820; }
  .video-player-wrap video, .video-player-wrap iframe { width: 100%; height: 100%; display: block; border: 0; object-fit: contain; }
  .video-card-status { position: absolute; top: 8px; left: 8px; padding: 5px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
  .video-card-status.active { color: #365844; background: #e8f1e6; }
  .video-card-status.inactive { color: #8b6557; background: #f5ece7; }
  .video-card-content { padding: 11px; }
  .video-card-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 7px; }
  .video-card-heading > div:first-child { min-width: 0; }
  .video-card-heading h3 { margin: 0 0 4px; font-size: 11px; line-height: 1.45; overflow-wrap: anywhere; }
  .video-card-heading > div:first-child > span { color: var(--muted); font-size: 9px; }
  .video-actions { display: flex; align-items: center; flex-wrap: wrap; justify-content: flex-end; gap: 5px; }
  .video-actions form { margin: 0; }
  .video-edit-button, .video-delete-button { width: 27px; height: 27px; display: grid; place-items: center; color: #526255; background: #f1f3ed; border: 0; border-radius: 6px; cursor: pointer; font-size: 10px; }
  .video-delete-button { color: #a34d40; background: #fff; border: 1px solid #ead9d0; }
  .video-status-button { min-height: 27px; padding: 0 7px; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font-size: 9px; font-weight: 700; white-space: nowrap; }
  .video-status-button.deactivate { color: #8b6557; background: #fff; border-color: #ead9d0; }
  .video-status-button.activate { color: #365844; background: #fff; border-color: #d5e6d2; }
  .video-caption { margin: 9px 0 0; color: #647064; font-size: 10px; line-height: 1.55; white-space: pre-line; overflow-wrap: anywhere; }
  .video-edit-dialog { width: min(500px, calc(100vw - 32px)); max-height: min(88vh, 800px); padding: 0; overflow-y: auto; color: var(--ink); background: #fffefa; border: 1px solid var(--line); border-radius: 13px; box-shadow: 0 24px 70px rgba(34,48,38,.24); }
  .video-edit-dialog::backdrop { background: rgba(28,40,32,.42); backdrop-filter: blur(2px); }
  .video-edit-heading { position: sticky; z-index: 1; top: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 17px 20px; background: #fffefa; border-bottom: 1px solid var(--line); }
  .video-edit-heading h2 { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
  .video-edit-heading p { margin: 0; color: var(--muted); font-size: 11px; }
  .video-edit-close { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #647064; background: #f1f3ed; border: 0; border-radius: 50%; cursor: pointer; }
  .video-edit-form { display: grid; gap: 15px; padding: 18px 20px 20px; }
  .video-edit-preview { width: 100%; max-height: 220px; background: #202820; border-radius: 9px; }
  .video-file-input { width: 100%; padding: 9px; border: 1px solid #e2e5dc; border-radius: 8px; font: inherit; font-size: 11px; }
  .video-empty { grid-column: 1 / -1; display: grid; justify-items: center; padding: 46px 20px; text-align: center; }
  .video-empty > span { width: 46px; height: 46px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .video-empty h3 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .video-empty p { margin: 0; color: var(--muted); font-size: 12px; }
  .video-pagination { display: flex; justify-content: center; padding: 12px 20px 18px; border-top: 1px solid var(--line); }
  .video-pagination .pagination-links { display: flex; align-items: center; gap: 5px; }
  .video-pagination .pagination-links a, .video-pagination .pagination-links > span { min-width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: #68756a; font-size: 11px; }
  .video-pagination .pagination-links [aria-current=page] { color: #fff; background: var(--green); border-color: var(--green); }
  .video-pagination svg { width: 15px; height: 15px; }
  @media (max-width: 900px) { .video-layout { grid-template-columns: 1fr; } }
  @media (max-width: 600px) { .video-list-scroll { grid-template-columns: 1fr; } .video-filter { flex-wrap: wrap; } .video-search-field { flex-basis: 100%; } }
</style>
<script>
  const syncVideoSourceFields = (container) => {
    const selected = container.querySelector('input[name="video_type"]:checked')?.value;
    container.querySelectorAll('[data-video-source]').forEach((field) => {
      const isSelected = field.dataset.videoSource === selected;
      field.hidden = !isSelected;
      field.querySelectorAll('input, textarea').forEach((input) => {
        input.disabled = !isSelected;
        const currentUploadExists = Boolean(container.closest('dialog')?.querySelector('[data-video-source="upload"] video'));
        input.required = isSelected && (
          input.name === 'youtube_url' ||
          (input.name === 'video' && (!container.closest('dialog') || !currentUploadExists))
        );
      });
    });
  };
  document.querySelectorAll('.video-form, .video-edit-form').forEach((form) => {
    form.addEventListener('change', (event) => {
      if (event.target instanceof HTMLInputElement && event.target.name === 'video_type') {
        syncVideoSourceFields(form);
      }
    });
    syncVideoSourceFields(form);
  });
  document.querySelectorAll('[data-open-video-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.openVideoEdit);
      if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-video-edit]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
  const videoInput = document.querySelector('#video');
  const videoFileName = document.querySelector('[data-video-file-name]');
  if (videoInput && videoFileName) {
    videoInput.addEventListener('change', () => {
      videoFileName.textContent = videoInput.files?.[0]?.name || 'No video selected';
    });
  }
</script>
@endsection
