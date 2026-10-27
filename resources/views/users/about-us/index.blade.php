@extends('users.layout.app')

@section('title', 'About Us')

@section('content')
@php
  $editorSections = old('sections', $aboutPage?->sections ?? []);
@endphp
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>About Us</div>
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
      <section class="page-heading" aria-labelledby="about-us-title">
        <div>
          <p class="eyebrow">Tell your care story</p>
          <h1 id="about-us-title">About Us</h1>
          <p class="page-subtitle">Build a page that fits your practice. Add only the sections you need and arrange them in your preferred order.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="about-us-alert" role="status">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="about-us-alert error" role="alert">Please review the highlighted fields before saving.</div>
      @endif

      <form class="about-us-form" method="POST" action="{{ route('user.about-us.save') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <section class="panel about-us-hero-panel" aria-labelledby="about-hero-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="about-hero-title">Page introduction</h2>
              <p class="panel-caption">This is the first section visitors see.</p>
            </div>
          </div>
          <div class="about-us-fields">
            <div class="about-us-field">
              <label for="hero-title">Main heading</label>
              <input id="hero-title" type="text" name="hero_title" value="{{ old('hero_title', $aboutPage?->hero_title ?? $user->name) }}" maxlength="200" required>
              @error('hero_title')<span class="about-us-error">{{ $message }}</span>@enderror
            </div>
            <div class="about-us-field">
              <label for="intro">Short introduction <span>(optional)</span></label>
              <textarea id="intro" name="intro" rows="3" maxlength="2000" placeholder="A short welcome or summary...">{{ old('intro', $aboutPage?->intro) }}</textarea>
              @error('intro')<span class="about-us-error">{{ $message }}</span>@enderror
            </div>
            <div class="about-us-field">
              <label for="hero-image">Hero image <span>(optional, JPG/PNG/WebP, max 5 MB)</span></label>
              <input class="about-us-file" id="hero-image" type="file" name="hero_image" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
              @error('hero_image')<span class="about-us-error">{{ $message }}</span>@enderror
              @if ($aboutPage?->hero_image_url)
                <img class="about-us-current-image" src="{{ $aboutPage->hero_image_url }}" alt="Current About Us hero">
              @endif
            </div>
          </div>
        </section>

        <section class="panel about-us-sections-panel" aria-labelledby="about-sections-title">
          <div class="panel-heading about-us-sections-heading">
            <div>
              <h2 class="panel-title" id="about-sections-title">Your content sections</h2>
              <p class="panel-caption">Add sections such as Our Story, Mission, Vision, Values, Team or Facilities. Empty sections are not required.</p>
            </div>
            <button class="about-us-add-section" type="button" data-add-about-section><i class="fas fa-plus"></i> Add section</button>
          </div>
          <div class="about-us-section-list" data-about-section-list>
            @foreach ($editorSections as $index => $section)
              <fieldset class="about-us-section">
                <legend>Section {{ $loop->iteration }}</legend>
                <input type="hidden" name="sections[{{ $index }}][key]" value="{{ $section['key'] ?? \Illuminate\Support\Str::uuid() }}">
                <div class="about-us-field">
                  <label for="section-title-{{ $index }}">Section heading</label>
                  <input id="section-title-{{ $index }}" type="text" name="sections[{{ $index }}][title]" value="{{ $section['title'] ?? '' }}" maxlength="160" placeholder="e.g. Our Mission" required>
                  @error("sections.{$index}.title")<span class="about-us-error">{{ $message }}</span>@enderror
                </div>
                <div class="about-us-field">
                  <label for="section-content-{{ $index }}">Section content</label>
                  <textarea id="section-content-{{ $index }}" name="sections[{{ $index }}][content]" rows="5" maxlength="10000" placeholder="Write this section's content..." required>{{ $section['content'] ?? '' }}</textarea>
                  @error("sections.{$index}.content")<span class="about-us-error">{{ $message }}</span>@enderror
                </div>
                <div class="about-us-section-bottom">
                  <div class="about-us-field">
                    <label for="section-image-{{ $index }}">Section image <span>(optional)</span></label>
                    <input class="about-us-file" id="section-image-{{ $index }}" type="file" name="sections[{{ $index }}][image]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    @error("sections.{$index}.image")<span class="about-us-error">{{ $message }}</span>@enderror
                    @if (!empty($section['image_path']))
                      <img class="about-us-current-image" src="{{ asset($section['image_path']) }}" alt="Current section image">
                    @endif
                  </div>
                  <div class="about-us-section-actions">
                    <button class="about-us-move-section" type="button" data-move-about-section="-1" aria-label="Move section up"><i class="fas fa-arrow-up"></i></button>
                    <button class="about-us-move-section" type="button" data-move-about-section="1" aria-label="Move section down"><i class="fas fa-arrow-down"></i></button>
                    <button class="about-us-remove-section" type="button" data-remove-about-section><i class="fas fa-trash-alt"></i> Remove section</button>
                  </div>
                </div>
              </fieldset>
            @endforeach
          </div>
          @error('sections')<span class="about-us-error about-us-general-error">{{ $message }}</span>@enderror
          <template id="about-us-section-template">
            <fieldset class="about-us-section">
              <legend>New section</legend>
              <input type="hidden" name="sections[__INDEX__][key]" value="__KEY__">
              <div class="about-us-field">
                <label for="section-title-__INDEX__">Section heading</label>
                <input id="section-title-__INDEX__" type="text" name="sections[__INDEX__][title]" maxlength="160" placeholder="e.g. Our Mission" required>
              </div>
              <div class="about-us-field">
                <label for="section-content-__INDEX__">Section content</label>
                <textarea id="section-content-__INDEX__" name="sections[__INDEX__][content]" rows="5" maxlength="10000" placeholder="Write this section's content..." required></textarea>
              </div>
              <div class="about-us-section-bottom">
                <div class="about-us-field">
                  <label for="section-image-__INDEX__">Section image <span>(optional)</span></label>
                  <input class="about-us-file" id="section-image-__INDEX__" type="file" name="sections[__INDEX__][image]" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                </div>
                <div class="about-us-section-actions">
                  <button class="about-us-move-section" type="button" data-move-about-section="-1" aria-label="Move section up"><i class="fas fa-arrow-up"></i></button>
                  <button class="about-us-move-section" type="button" data-move-about-section="1" aria-label="Move section down"><i class="fas fa-arrow-down"></i></button>
                  <button class="about-us-remove-section" type="button" data-remove-about-section><i class="fas fa-trash-alt"></i> Remove section</button>
                </div>
              </div>
            </fieldset>
          </template>
        </section>

        <div class="about-us-save-row">
          <span>Each account has its own About Us content and images.</span>
          <button class="about-us-save" type="submit"><i class="fas fa-save"></i> Save About Us page</button>
        </div>
      </form>
    </div>
  </div>
</main>

<style>
  .about-us-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .about-us-alert.error { color: #8b5147; background: #fbefec; border-color: #efd7d1; }
  .about-us-form { display: grid; gap: 18px; }
  .about-us-hero-panel, .about-us-sections-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .about-us-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; padding: 0 20px 20px; }
  .about-us-field { min-width: 0; display: grid; align-content: start; gap: 7px; }
  .about-us-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .about-us-field label span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .about-us-field input:not([type=file]), .about-us-field textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .about-us-field input:focus, .about-us-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .about-us-field textarea { resize: vertical; line-height: 1.6; }
  .about-us-file { width: 100%; padding: 9px; border: 1px solid #e2e5dc; border-radius: 8px; font: inherit; font-size: 11px; }
  .about-us-current-image { width: 100%; max-height: 180px; object-fit: cover; border: 1px solid var(--line); border-radius: 8px; }
  .about-us-error { color: #a34d40; font-size: 11px; }
  .about-us-general-error { display: block; padding: 0 20px 16px; }
  .about-us-sections-heading { display: flex; align-items: center; justify-content: space-between; gap: 14px; }
  .about-us-add-section, .about-us-remove-section { min-height: 36px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 0 11px; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; white-space: nowrap; }
  .about-us-add-section { color: #fff; background: var(--green-dark); border: 0; }
  .about-us-add-section:hover, .about-us-save:hover { background: var(--green); }
  .about-us-section-list { display: grid; gap: 14px; padding: 0 20px 20px; }
  .about-us-section { min-width: 0; display: grid; gap: 13px; margin: 0; padding: 16px; border: 1px solid var(--line); border-radius: 10px; }
  .about-us-section legend { padding: 0 7px; color: #536658; font-size: 11px; font-weight: 700; }
  .about-us-section-bottom { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; }
  .about-us-section-bottom > .about-us-field { flex: 1; }
  .about-us-section-actions { display: flex; align-items: center; gap: 6px; }
  .about-us-move-section { width: 34px; height: 36px; display: grid; place-items: center; color: #526255; background: #f1f3ed; border: 0; border-radius: 7px; cursor: pointer; }
  .about-us-remove-section { color: #a34d40; background: #fff; border: 1px solid #ead9d0; }
  .about-us-remove-section:hover { color: #fff; background: #a34d40; }
  .about-us-save-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 15px 18px; background: #fff; border: 1px solid var(--line); border-radius: 11px; box-shadow: var(--shadow); }
  .about-us-save-row > span { color: var(--muted); font-size: 11px; }
  .about-us-save { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 16px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  @media (max-width: 700px) { .about-us-fields { grid-template-columns: 1fr; } .about-us-sections-heading, .about-us-section-bottom, .about-us-save-row { align-items: stretch; flex-direction: column; } .about-us-add-section, .about-us-remove-section, .about-us-save { width: 100%; } .about-us-section-actions { justify-content: flex-end; } }
</style>
<script>
  const aboutSectionList = document.querySelector('[data-about-section-list]');
  const aboutSectionTemplate = document.querySelector('#about-us-section-template');
  const addAboutSectionButton = document.querySelector('[data-add-about-section]');
  let aboutSectionIndex = {{ count($editorSections) }};

  if (aboutSectionList && aboutSectionTemplate && addAboutSectionButton) {
    addAboutSectionButton.addEventListener('click', () => {
      if (aboutSectionList.querySelectorAll('.about-us-section').length >= 20) {
        window.alert('You can add up to 20 sections.');
        return;
      }

      const key = window.crypto?.randomUUID
        ? window.crypto.randomUUID()
        : `section-${Date.now()}-${Math.random().toString(16).slice(2)}`;
      const markup = aboutSectionTemplate.innerHTML
        .replaceAll('__INDEX__', String(aboutSectionIndex++))
        .replaceAll('__KEY__', key);
      aboutSectionList.insertAdjacentHTML('beforeend', markup);
      const sections = aboutSectionList.querySelectorAll('.about-us-section');
      sections[sections.length - 1]?.querySelector('legend')?.replaceChildren(`Section ${sections.length}`);
    });

    aboutSectionList.addEventListener('click', (event) => {
      const button = event.target.closest('[data-remove-about-section]');
      if (button) {
        button.closest('.about-us-section')?.remove();
        updateAboutSectionLegends();
        return;
      }

      const moveButton = event.target.closest('[data-move-about-section]');
      const currentSection = moveButton?.closest('.about-us-section');
      if (!moveButton || !currentSection) return;

      if (moveButton.dataset.moveAboutSection === '-1' && currentSection.previousElementSibling) {
        aboutSectionList.insertBefore(currentSection, currentSection.previousElementSibling);
      } else if (moveButton.dataset.moveAboutSection === '1' && currentSection.nextElementSibling) {
        aboutSectionList.insertBefore(currentSection.nextElementSibling, currentSection);
      }
      updateAboutSectionLegends();
    });

    updateAboutSectionLegends();
  }

  function updateAboutSectionLegends() {
    aboutSectionList?.querySelectorAll('.about-us-section').forEach((section, index) => {
      section.querySelector('legend')?.replaceChildren(`Section ${index + 1}`);
    });
  }
</script>
@endsection
