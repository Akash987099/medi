@extends('users.layout.app')

@section('title', 'Services')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Services</div>
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
      <section class="page-heading" aria-labelledby="services-title">
        <div>
          <p class="eyebrow">Your care, your services</p>
          <h1 id="services-title">Services</h1>
          <p class="page-subtitle">Services from your category are shown here. Changes and new services are saved only to your account.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="user-service-alert" role="status">{{ session('status') }}</div>
      @endif

      <section class="user-service-create panel" aria-labelledby="add-own-service-title">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title" id="add-own-service-title">Add your own service</h2>
            <p class="panel-caption">This service belongs to your account and will not change the admin service list.</p>
          </div>
        </div>
        <form class="user-service-form" method="POST" action="{{ route('user.services.store') }}" enctype="multipart/form-data">
          @csrf
          <div class="user-service-field">
            <label for="own-service-title">Service title</label>
            <input id="own-service-title" type="text" name="title" value="{{ old('title') }}" maxlength="120" placeholder="e.g. Home care visits" required>
            @error('title')<span class="user-service-error">{{ $message }}</span>@enderror
          </div>
          <div class="user-service-field">
            <label for="own-service-content">Description <span>(optional)</span></label>
            <textarea id="own-service-content" name="content" rows="3" maxlength="5000" placeholder="Describe the service...">{{ old('content') }}</textarea>
            @error('content')<span class="user-service-error">{{ $message }}</span>@enderror
          </div>
          <div class="user-service-field">
            <label for="own-service-image">Image <span>(optional, JPG/PNG/WebP, max 5 MB)</span></label>
            <input class="user-service-file" id="own-service-image" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
            @error('image')<span class="user-service-error">{{ $message }}</span>@enderror
          </div>
          <button class="user-service-submit" type="submit"><i class="fas fa-plus"></i> Add service</button>
        </form>
      </section>

      @if ($categories->isEmpty())
        <section class="user-service-empty panel">
          <span><i class="fas fa-hand-holding-medical"></i></span>
          <h2>No category services assigned</h2>
          <p>Admin services will appear here when your doctor or hospital profile is linked to an active category. You can still add your own service above.</p>
        </section>
      @else
        @foreach ($categories as $category)
          <section class="user-service-category panel" aria-labelledby="category-services-{{ $category->id }}">
            <div class="panel-heading">
              <div>
                <h2 class="panel-title" id="category-services-{{ $category->id }}">{{ $category->name }}</h2>
                <p class="panel-caption">Category services assigned to your workspace</p>
              </div>
            </div>
            <div class="user-service-grid">
              @foreach ($services->where('category_id', $category->id) as $service)
                @php
                  $override = $overrides->get($service->id);
                  $serviceTitle = $override?->title ?: $service->name;
                  $serviceIsActive = $override?->is_active ?? true;
                @endphp
                <article class="user-service-card">
                  <div class="user-service-visual">
                    @if ($override?->image_url)
                      <img src="{{ $override->image_url }}" alt="{{ $serviceTitle }}" loading="lazy">
                    @elseif ($service->visual_type === 'image')
                      <img src="{{ asset('storage/'.$service->visual_value) }}" alt="{{ $serviceTitle }}" loading="lazy">
                    @elseif ($service->visual_type === 'svg')
                      {!! $service->visual_value !!}
                    @else
                      <i class="{{ $service->visual_value }}" aria-hidden="true"></i>
                    @endif
                    <span class="user-service-status {{ $serviceIsActive ? 'active' : 'inactive' }}">{{ $serviceIsActive ? 'Active' : 'Inactive' }}</span>
                  </div>
                  <div class="user-service-card-content">
                    <div class="user-service-card-heading">
                      <h3>{{ $serviceTitle }}</h3>
                      <div class="user-service-actions">
                        <button class="user-service-edit" type="button" data-open-catalog-edit="catalog-service-dialog-{{ $service->id }}" aria-label="Edit {{ $serviceTitle }}"><i class="fas fa-pen"></i></button>
                        <form method="POST" action="{{ route('user.services.catalog.status', $service->id) }}">
                          @csrf
                          @method('PATCH')
                          <button class="user-service-status-button {{ $serviceIsActive ? 'deactivate' : 'activate' }}" type="submit">{{ $serviceIsActive ? 'Deactivate' : 'Activate' }}</button>
                        </form>
                      </div>
                    </div>
                    @if ($override?->content)
                      <p class="user-service-description">{{ $override->content }}</p>
                    @else
                      <p class="user-service-original">Admin catalog service · edits here only affect your account.</p>
                    @endif
                  </div>
                  <dialog class="user-service-dialog" id="catalog-service-dialog-{{ $service->id }}" aria-labelledby="catalog-service-title-{{ $service->id }}">
                    <div class="user-service-dialog-heading">
                      <div><h2 id="catalog-service-title-{{ $service->id }}">Customize service</h2><p>Your changes are stored separately from the admin service.</p></div>
                      <button class="user-service-close" type="button" data-close-user-service aria-label="Close edit service"><i class="fas fa-times"></i></button>
                    </div>
                    <form class="user-service-edit-form" method="POST" action="{{ route('user.services.catalog.update', $service->id) }}" enctype="multipart/form-data">
                      @csrf
                      @method('PUT')
                      <div class="user-service-field">
                        <label for="catalog-title-{{ $service->id }}">Service title</label>
                        <input id="catalog-title-{{ $service->id }}" type="text" name="title" value="{{ $serviceTitle }}" maxlength="120" required>
                      </div>
                      <div class="user-service-field">
                        <label for="catalog-content-{{ $service->id }}">Your service description</label>
                        <textarea id="catalog-content-{{ $service->id }}" name="content" rows="5" maxlength="5000" placeholder="Add details for your customers...">{{ $override?->content }}</textarea>
                      </div>
                      <div class="user-service-field">
                        <label for="catalog-image-{{ $service->id }}">Your image <span>(optional, replaces the catalog visual for your account)</span></label>
                        <input class="user-service-file" id="catalog-image-{{ $service->id }}" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                      </div>
                      <button class="user-service-submit" type="submit">Save my version</button>
                    </form>
                  </dialog>
                </article>
              @endforeach
              @if ($services->where('category_id', $category->id)->isEmpty())
                <p class="user-service-no-results">No active services are currently available in this category.</p>
              @endif
            </div>
          </section>
        @endforeach
      @endif

      @if ($customServices->isNotEmpty())
        <section class="user-service-category panel" aria-labelledby="your-services-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="your-services-title">Your services</h2>
              <p class="panel-caption">Services created by your account</p>
            </div>
          </div>
          <div class="user-service-grid">
            @foreach ($customServices as $customService)
              <article class="user-service-card">
                <div class="user-service-visual">
                  @if ($customService->image_url)
                    <img src="{{ $customService->image_url }}" alt="{{ $customService->title }}" loading="lazy">
                  @else
                    <i class="fas fa-hand-holding-medical" aria-hidden="true"></i>
                  @endif
                  <span class="user-service-status {{ $customService->is_active ? 'active' : 'inactive' }}">{{ $customService->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="user-service-card-content">
                  <div class="user-service-card-heading">
                    <h3>{{ $customService->title }}</h3>
                    <div class="user-service-actions">
                      <button class="user-service-edit" type="button" data-open-custom-edit="custom-service-dialog-{{ $customService->id }}" aria-label="Edit {{ $customService->title }}"><i class="fas fa-pen"></i></button>
                      <form method="POST" action="{{ route('user.services.custom.status', $customService->id) }}">
                        @csrf
                        @method('PATCH')
                        <button class="user-service-status-button {{ $customService->is_active ? 'deactivate' : 'activate' }}" type="submit">{{ $customService->is_active ? 'Deactivate' : 'Activate' }}</button>
                      </form>
                      <form method="POST" action="{{ route('user.services.custom.destroy', $customService->id) }}" onsubmit="return confirm('Delete your service permanently?')">
                        @csrf
                        @method('DELETE')
                        <button class="user-service-delete" type="submit" aria-label="Delete {{ $customService->title }}"><i class="fas fa-trash-alt"></i></button>
                      </form>
                    </div>
                  </div>
                  @if ($customService->content)
                    <p class="user-service-description">{{ $customService->content }}</p>
                  @endif
                </div>
                <dialog class="user-service-dialog" id="custom-service-dialog-{{ $customService->id }}" aria-labelledby="custom-service-title-{{ $customService->id }}">
                  <div class="user-service-dialog-heading">
                    <div><h2 id="custom-service-title-{{ $customService->id }}">Edit your service</h2><p>This service belongs to your account.</p></div>
                    <button class="user-service-close" type="button" data-close-user-service aria-label="Close edit service"><i class="fas fa-times"></i></button>
                  </div>
                  <form class="user-service-edit-form" method="POST" action="{{ route('user.services.custom.update', $customService->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="user-service-field">
                      <label for="custom-title-{{ $customService->id }}">Service title</label>
                      <input id="custom-title-{{ $customService->id }}" type="text" name="title" value="{{ $customService->title }}" maxlength="120" required>
                    </div>
                    <div class="user-service-field">
                      <label for="custom-content-{{ $customService->id }}">Description</label>
                      <textarea id="custom-content-{{ $customService->id }}" name="content" rows="5" maxlength="5000">{{ $customService->content }}</textarea>
                    </div>
                    <div class="user-service-field">
                      <label for="custom-image-{{ $customService->id }}">Replace image <span>(optional)</span></label>
                      <input class="user-service-file" id="custom-image-{{ $customService->id }}" type="file" name="image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    </div>
                    <button class="user-service-submit" type="submit">Save changes</button>
                  </form>
                </dialog>
              </article>
            @endforeach
          </div>
        </section>
      @endif
    </div>
  </div>
</main>

<style>
  .user-service-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .user-service-create, .user-service-category, .user-service-empty { margin-bottom: 20px; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .user-service-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: end; gap: 13px; padding: 0 20px 20px; }
  .user-service-field { min-width: 0; display: grid; gap: 7px; }
  .user-service-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .user-service-field label span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .user-service-field input:not([type=file]), .user-service-field textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .user-service-field input:focus, .user-service-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .user-service-field textarea { resize: vertical; line-height: 1.6; }
  .user-service-file { width: 100%; padding: 9px; border: 1px solid #e2e5dc; border-radius: 8px; font: inherit; font-size: 11px; }
  .user-service-error { color: #a34d40; font-size: 11px; }
  .user-service-submit { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .user-service-submit:hover { background: var(--green); }
  .user-service-category .panel-heading { margin-bottom: 0; }
  .user-service-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; padding: 0 18px 18px; }
  .user-service-card { min-width: 0; overflow: hidden; background: #fff; border: 1px solid var(--line); border-radius: 11px; }
  .user-service-visual { position: relative; height: 145px; display: grid; place-items: center; overflow: hidden; color: var(--green); background: #f3f6f0; font-size: 40px; }
  .user-service-visual img { width: 100%; height: 100%; object-fit: cover; }
  .user-service-visual svg { max-width: 70px; max-height: 70px; }
  .user-service-status { position: absolute; top: 9px; left: 9px; padding: 5px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; }
  .user-service-status.active { color: #365844; background: #e8f1e6; }
  .user-service-status.inactive { color: #8b6557; background: #f5ece7; }
  .user-service-card-content { padding: 12px; }
  .user-service-card-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
  .user-service-card-heading h3 { margin: 2px 0 0; font-size: 12px; line-height: 1.5; overflow-wrap: anywhere; }
  .user-service-actions { display: flex; align-items: center; flex-wrap: wrap; justify-content: flex-end; gap: 5px; }
  .user-service-actions form { margin: 0; }
  .user-service-edit, .user-service-delete { width: 28px; height: 28px; display: grid; place-items: center; border-radius: 6px; cursor: pointer; font-size: 10px; }
  .user-service-edit { color: #526255; background: #f1f3ed; border: 0; }
  .user-service-delete { color: #a34d40; background: #fff; border: 1px solid #ead9d0; }
  .user-service-status-button { min-height: 28px; padding: 0 8px; border: 1px solid transparent; border-radius: 6px; cursor: pointer; font-size: 9px; font-weight: 700; white-space: nowrap; }
  .user-service-status-button.deactivate { color: #8b6557; background: #fff; border-color: #ead9d0; }
  .user-service-status-button.activate { color: #365844; background: #fff; border-color: #d5e6d2; }
  .user-service-description, .user-service-original { margin: 10px 0 0; color: #647064; font-size: 11px; line-height: 1.6; white-space: pre-line; overflow-wrap: anywhere; }
  .user-service-original { color: var(--muted); font-size: 10px; }
  .user-service-no-results { grid-column: 1 / -1; margin: 0; padding: 10px 0 4px; color: var(--muted); font-size: 12px; }
  .user-service-empty { display: grid; justify-items: center; padding: 38px 20px; text-align: center; }
  .user-service-empty > span { width: 48px; height: 48px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; font-size: 19px; }
  .user-service-empty h2 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 18px; font-weight: 600; }
  .user-service-empty p { max-width: 560px; margin: 0; color: var(--muted); font-size: 12px; line-height: 1.6; }
  .user-service-dialog { width: min(520px, calc(100vw - 32px)); max-height: min(88vh, 780px); padding: 0; overflow-y: auto; color: var(--ink); background: #fffefa; border: 1px solid var(--line); border-radius: 13px; box-shadow: 0 24px 70px rgba(34,48,38,.24); }
  .user-service-dialog::backdrop { background: rgba(28,40,32,.42); backdrop-filter: blur(2px); }
  .user-service-dialog-heading { position: sticky; z-index: 1; top: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 17px 20px; background: #fffefa; border-bottom: 1px solid var(--line); }
  .user-service-dialog-heading h2 { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
  .user-service-dialog-heading p { margin: 0; color: var(--muted); font-size: 11px; }
  .user-service-close { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #647064; background: #f1f3ed; border: 0; border-radius: 50%; cursor: pointer; }
  .user-service-edit-form { display: grid; gap: 15px; padding: 18px 20px 20px; }
  @media (max-width: 1000px) { .user-service-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 700px) { .user-service-form { grid-template-columns: 1fr; } .user-service-grid { grid-template-columns: 1fr; } }
</style>
<script>
  document.querySelectorAll('[data-open-catalog-edit], [data-open-custom-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialogId = button.dataset.openCatalogEdit || button.dataset.openCustomEdit;
      const dialog = document.getElementById(dialogId);
      if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-user-service]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
</script>
@endsection
