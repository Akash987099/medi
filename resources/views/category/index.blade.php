@extends('layout.app')

@section('title', 'Categories')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Masters</strong><span> / </span>Categories</div>
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
          <h1>Categories</h1>
          <p class="page-subtitle">Manage the categories used across your clinic.</p>
        </div>
        <button class="category-add-button" type="button" data-open-category-modal>
          <i class="fas fa-plus"></i> Add category
        </button>
      </div>

      @if (session('status'))
        <div class="category-alert" role="status">{{ session('status') }}</div>
      @endif

      @if ($errors->any())
        <div class="category-alert error" role="alert">Please review the highlighted fields and try again.</div>
      @endif

      <section class="panel" aria-label="Category list">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title">All categories</h2>
            <p class="panel-caption">{{ $categories->total() }} {{ $categories->total() === 1 ? 'category' : 'categories' }} in your clinic</p>
          </div>
        </div>
        <div class="category-table-wrap">
          <table class="category-table">
            <thead>
              <tr>
                <th scope="col">Category</th>
                <th scope="col">Visual type</th>
                <th scope="col">Status</th>
                <th scope="col">Date added</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($categories as $category)
                <tr>
                  <td>
                    <div class="category-name-cell">
                      <span class="category-visual" aria-hidden="true">
                        @if ($category->visual_type === 'image')
                          <img src="{{ asset('storage/'.$category->visual_value) }}" alt="">
                        @elseif ($category->visual_type === 'svg')
                          {!! $category->visual_value !!}
                        @else
                          <i class="{{ $category->visual_value }}"></i>
                        @endif
                      </span>
                      {{ $category->name }}
                    </div>
                  </td>
                  <td><span class="category-type">{{ $category->visual_type }}</span></td>
                  <td><span class="category-status {{ $category->is_active ? 'active' : 'inactive' }}">{{ $category->is_active ? 'Active' : 'Inactive' }}</span></td>
                  <td class="category-date">{{ $category->created_at->format('M j, Y') }}</td>
                  <td>
                    <div class="category-actions">
                      <button
                        class="category-icon-button"
                        type="button"
                        title="Edit category"
                        aria-label="Edit {{ $category->name }}"
                        data-edit-category
                        data-category-id="{{ $category->id }}"
                        data-category-name="{{ $category->name }}"
                        data-visual-type="{{ $category->visual_type }}"
                        data-visual-value="{{ $category->visual_value }}"
                      ><i class="far fa-edit"></i></button>
                      <form action="{{ route('category.status', $category) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button
                          class="category-icon-button {{ $category->is_active ? 'deactivate' : 'activate' }}"
                          type="submit"
                          title="{{ $category->is_active ? 'Deactivate category' : 'Activate category' }}"
                          aria-label="{{ $category->is_active ? 'Deactivate' : 'Activate' }} {{ $category->name }}"
                        ><i class="fas {{ $category->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td class="category-empty" colspan="5">
                    <i class="far fa-folder-open"></i>
                    No categories yet. Add your first category to get started.
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($categories->hasPages())
          <div class="category-pagination">{{ $categories->links('vendor.pagination.compact') }}</div>
        @endif
      </section>

      <p class="footer-note"><i class="fas fa-leaf"></i> Thoughtful care, one visit at a time.</p>
    </div>
  </div>
</main>

<div class="category-modal" id="category-modal" @if (!$errors->any()) hidden @endif>
  <section class="category-dialog" role="dialog" aria-modal="true" aria-labelledby="category-modal-title">
    <div class="category-dialog-header">
      <div>
        <h2 id="category-modal-title">{{ old('category_id') ? 'Edit category' : 'Add a category' }}</h2>
        <p>Give your category a name and choose one visual.</p>
      </div>
      <button class="category-modal-close" type="button" aria-label="Close dialog" data-close-category-modal><i class="fas fa-times"></i></button>
    </div>

    <form
      class="category-form"
      id="category-form"
      action="{{ old('category_id') ? route('category.update', old('category_id')) : route('category.store') }}"
      method="POST"
      enctype="multipart/form-data"
      data-edit-mode="{{ old('category_id') ? 'true' : 'false' }}"
      data-create-action="{{ route('category.store') }}"
      data-update-base="{{ url('/category') }}"
    >
      @csrf
      <input type="hidden" name="_method" value="PUT" @disabled(!old('category_id'))>
      <input type="hidden" name="category_id" value="{{ old('category_id') }}">
      <div class="category-field">
        <label for="category-name">Category name</label>
        <input class="category-input" id="category-name" name="name" type="text" value="{{ old('name') }}" maxlength="120" placeholder="e.g. Cardiology" required>
        @error('name')<p class="category-error">{{ $message }}</p>@enderror
      </div>

      <fieldset class="category-field category-options-fieldset">
        <legend class="category-field-label">Choose one visual</legend>
        <div class="visual-options">
          @php($selectedVisualType = old('visual_type', 'icon'))
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

        <div class="visual-panel" data-visual-panel="image" @if ($selectedVisualType !== 'image') hidden @endif>
          <label class="category-field-label" for="category-image">Choose an image</label>
          <input class="file-input" id="category-image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
          <p class="category-help" data-image-help>JPG, PNG, or WebP; maximum file size 2 MB.</p>
          @error('image')<p class="category-error">{{ $message }}</p>@enderror
        </div>

        <div class="visual-panel" data-visual-panel="svg" @if ($selectedVisualType !== 'svg') hidden @endif>
          <label class="category-field-label" for="category-svg">Paste SVG markup</label>
          <textarea class="category-textarea" id="category-svg" name="svg_code" maxlength="20000" placeholder="<svg viewBox=&quot;0 0 24 24&quot;>...</svg>">{{ old('svg_code') }}</textarea>
          <p class="category-help">For safety, scripts, links, and unsupported SVG elements are removed when saved.</p>
          @error('svg_code')<p class="category-error">{{ $message }}</p>@enderror
        </div>

        <div class="visual-panel" data-visual-panel="icon" @if ($selectedVisualType !== 'icon') hidden @endif>
          <label class="category-field-label" for="category-icon">Font Awesome icon class</label>
          <input class="category-input" id="category-icon" name="icon_class" type="text" value="{{ old('icon_class', old('visual_type') === 'icon' ? old('visual_value', 'fas fa-heart') : 'fas fa-heart') }}" maxlength="80" placeholder="fas fa-heart">
          <p class="category-help">Use one class such as <code>fas fa-heart</code>, <code>far fa-star</code>, or <code>fab fa-accessible-icon</code>.</p>
          @error('icon_class')<p class="category-error">{{ $message }}</p>@enderror
        </div>
      </fieldset>

      <div class="category-form-actions">
        <button class="category-cancel" type="button" data-close-category-modal>Cancel</button>
        <button class="category-save" type="submit">Save category</button>
      </div>
    </form>
  </section>
</div>
@endsection
