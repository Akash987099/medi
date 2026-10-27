@extends('layout.app')

@section('title', 'Services')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Masters</strong><span> / </span>Services</div>
    </div>
    <div class="topbar-actions">
      <button class="icon-button" type="button" aria-label="Notifications">
        <i class="far fa-bell"></i><span class="notification-dot"></span>
      </button>
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
      <div class="category-toolbar">
        <div>
          <p class="eyebrow">Organize your care</p>
          <h1>Services</h1>
          <p class="page-subtitle">Manage clinic services and organize them by category.</p>
        </div>
        <button class="category-add-button" type="button" data-open-service-modal>
          <i class="fas fa-plus"></i> Add service
        </button>
      </div>

      @if (session('status'))
        <div class="category-alert" role="status">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="category-alert error" role="alert">Please review the highlighted fields and try again.</div>
      @endif

      <section class="panel" aria-label="Service list">
        <div class="panel-heading service-list-heading">
          <div>
            <h2 class="panel-title">All services</h2>
            <p class="panel-caption">{{ $services->total() }} {{ $services->total() === 1 ? 'service' : 'services' }} in your clinic</p>
          </div>
          <form class="service-filter" action="{{ route('service.index') }}" method="GET">
            <label class="sr-only" for="service-category-filter">Filter services by category</label>
            <select class="category-input service-filter-select" id="service-category-filter" name="category_id">
              <option value="">All categories</option>
              @foreach ($activeCategories as $category)
                <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>{{ $category->name }}</option>
              @endforeach
            </select>
            <button class="category-filter-button" type="submit"><i class="fas fa-filter"></i> Apply</button>
            <a class="category-reset-button" href="{{ route('service.index') }}"><i class="fas fa-undo"></i> Reset</a>
          </form>
        </div>
        <div class="category-table-wrap">
          <table class="category-table">
            <thead>
              <tr>
                <th scope="col">Service</th>
                <th scope="col">Category</th>
                <th scope="col">Visual type</th>
                <th scope="col">Status</th>
                <th scope="col">Date added</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($services as $service)
                <tr>
                  <td>
                    <div class="category-name-cell">
                      <span class="category-visual" aria-hidden="true">
                        @if ($service->visual_type === 'image')
                          <img src="{{ asset('storage/'.$service->visual_value) }}" alt="">
                        @elseif ($service->visual_type === 'svg')
                          {!! $service->visual_value !!}
                        @else
                          <i class="{{ $service->visual_value }}"></i>
                        @endif
                      </span>
                      {{ $service->name }}
                    </div>
                  </td>
                  <td>{{ $service->category->name }}</td>
                  <td><span class="category-type">{{ $service->visual_type }}</span></td>
                  <td><span class="category-status {{ $service->is_active ? 'active' : 'inactive' }}">{{ $service->is_active ? 'Active' : 'Inactive' }}</span></td>
                  <td class="category-date">{{ $service->created_at->format('M j, Y') }}</td>
                  <td>
                    <div class="category-actions">
                      <button
                        class="category-icon-button"
                        type="button"
                        title="Edit service"
                        aria-label="Edit {{ $service->name }}"
                        data-edit-service
                        data-service-id="{{ $service->id }}"
                        data-service-name="{{ $service->name }}"
                        data-category-id="{{ $service->category_id }}"
                        data-visual-type="{{ $service->visual_type }}"
                        data-visual-value="{{ $service->visual_value }}"
                      ><i class="far fa-edit"></i></button>
                      <form action="{{ route('service.status', $service) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        @if ($selectedCategoryId)
                          <input type="hidden" name="category_id" value="{{ $selectedCategoryId }}">
                        @endif
                        <button
                          class="category-icon-button {{ $service->is_active ? 'deactivate' : 'activate' }}"
                          type="submit"
                          title="{{ $service->is_active ? 'Deactivate service' : 'Activate service' }}"
                          aria-label="{{ $service->is_active ? 'Deactivate' : 'Activate' }} {{ $service->name }}"
                        ><i class="fas {{ $service->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td class="category-empty" colspan="6">
                    <i class="far fa-folder-open"></i>
                    No services yet. Add a service to get started.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($services->hasPages())
          <div class="category-pagination">{{ $services->links('vendor.pagination.compact') }}</div>
        @endif
      </section>

      <p class="footer-note"><i class="fas fa-leaf"></i> Thoughtful care, one visit at a time.</p>
    </div>
  </div>
</main>

<div class="category-modal" id="service-modal" @if (!$errors->any()) hidden @endif>
  <section class="category-dialog" role="dialog" aria-modal="true" aria-labelledby="service-modal-title">
    <div class="category-dialog-header">
      <div>
        <h2 id="service-modal-title">{{ old('service_id') ? 'Edit service' : 'Add a service' }}</h2>
        <p>Select a category, name your service, and choose one visual.</p>
      </div>
      <button class="category-modal-close" type="button" aria-label="Close dialog" data-close-service-modal><i class="fas fa-times"></i></button>
    </div>

    <form
      class="category-form"
      id="service-form"
      action="{{ old('service_id') ? route('service.update', old('service_id')) : route('service.store') }}"
      method="POST"
      enctype="multipart/form-data"
      data-edit-mode="{{ old('service_id') ? 'true' : 'false' }}"
      data-create-action="{{ route('service.store') }}"
      data-update-base="{{ url('/service') }}"
      data-filter-category="{{ $selectedCategoryId }}"
    >
      @csrf
      <input type="hidden" name="_method" value="PUT" @disabled(!old('service_id'))>
      <input type="hidden" name="service_id" value="{{ old('service_id') }}">
      <div class="category-field">
        <label for="service-category">Category</label>
        <select class="category-input" id="service-category" name="category_id" required>
          <option value="">Select a category</option>
          @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
          @endforeach
        </select>
        @if ($activeCategories->isEmpty())
          <p class="category-help">Add and activate a category before creating a service.</p>
        @endif
        @error('category_id')<p class="category-error">{{ $message }}</p>@enderror
      </div>

      <div class="category-field">
        <label for="service-name">Service name</label>
        <input class="category-input" id="service-name" name="name" type="text" value="{{ old('name') }}" maxlength="120" placeholder="e.g. ECG" required>
        @error('name')<p class="category-error">{{ $message }}</p>@enderror
      </div>

      <fieldset class="category-field category-options-fieldset">
        <legend class="category-field-label">Choose one visual</legend>
        @php($selectedVisualType = old('visual_type', 'icon'))
        <div class="visual-options">
          <label class="visual-option">
            <input type="radio" name="visual_type" value="image" required @checked($selectedVisualType === 'image')>
            <span class="visual-option-card"><i class="far fa-image"></i>Upload image</span>
          </label>
          <label class="visual-option">
            <input type="radio" name="visual_type" value="svg" required @checked($selectedVisualType === 'svg')>
            <span class="visual-option-card"><i class="fas fa-code"></i>SVG code</span>
          </label>
          <label class="visual-option">
            <input type="radio" name="visual_type" value="icon" required @checked($selectedVisualType === 'icon')>
            <span class="visual-option-card"><i class="far fa-star"></i>Icon class</span>
          </label>
        </div>
        @error('visual_type')<p class="category-error">{{ $message }}</p>@enderror

        <div class="visual-panel" data-service-visual-panel="image" @if ($selectedVisualType !== 'image') hidden @endif>
          <label class="category-field-label" for="service-image">Choose an image</label>
          <input class="file-input" id="service-image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
          <p class="category-help" data-service-image-help>JPG, PNG, or WebP; maximum file size 2 MB.</p>
          @error('image')<p class="category-error">{{ $message }}</p>@enderror
        </div>

        <div class="visual-panel" data-service-visual-panel="svg" @if ($selectedVisualType !== 'svg') hidden @endif>
          <label class="category-field-label" for="service-svg">Paste SVG markup</label>
          <textarea class="category-textarea" id="service-svg" name="svg_code" maxlength="20000" placeholder="<svg viewBox=&quot;0 0 24 24&quot;>...</svg>">{{ old('svg_code', old('visual_type') === 'svg' ? old('visual_value') : '') }}</textarea>
          <p class="category-help">Scripts, links, and unsupported SVG elements are removed when saved.</p>
          @error('svg_code')<p class="category-error">{{ $message }}</p>@enderror
        </div>

        <div class="visual-panel" data-service-visual-panel="icon" @if ($selectedVisualType !== 'icon') hidden @endif>
          <label class="category-field-label" for="service-icon">Font Awesome icon class</label>
          <input class="category-input" id="service-icon" name="icon_class" type="text" value="{{ old('icon_class', old('visual_type') === 'icon' ? old('visual_value', 'fas fa-heart') : 'fas fa-heart') }}" maxlength="80" placeholder="fas fa-heart">
          <p class="category-help">For example: <code>fas fa-heart</code> or <code>far fa-star</code>.</p>
          @error('icon_class')<p class="category-error">{{ $message }}</p>@enderror
        </div>
      </fieldset>

      <div class="category-form-actions">
        <button class="category-cancel" type="button" data-close-service-modal>Cancel</button>
        <button class="category-save" type="submit" @disabled($activeCategories->isEmpty())>Save service</button>
      </div>
    </form>
  </section>
</div>
@endsection
