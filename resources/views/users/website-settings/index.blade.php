@extends('users.layout.app')

@section('title', 'Website Settings')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Website Settings</div>
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
      <section class="page-heading" aria-labelledby="website-settings-title">
        <div>
          <p class="eyebrow">Your website identity and contact details</p>
          <h1 id="website-settings-title">Website Settings</h1>
          <p class="page-subtitle">Manage your website logos, branding, contact information and footer details. These settings belong only to your account.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="website-settings-alert" role="status">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="website-settings-alert error" role="alert">Please review the highlighted fields before saving.</div>
      @endif

      <form class="website-settings-form" method="POST" action="{{ route('user.website-settings.save') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <section class="panel website-settings-panel" aria-labelledby="branding-settings-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="branding-settings-title">Website branding</h2>
              <p class="panel-caption">Set the name visitors see and upload separate header and footer logos.</p>
            </div>
          </div>
          <div class="website-settings-grid">
            <div class="website-settings-field">
              <label for="site-name">Website / clinic name</label>
              <input id="site-name" type="text" name="site_name" value="{{ old('site_name', $settings?->site_name ?? $user->name) }}" maxlength="160" required>
              @error('site_name')<span class="website-settings-error">{{ $message }}</span>@enderror
            </div>
            <div class="website-settings-field">
              <label for="tagline">Tagline <span>(optional)</span></label>
              <input id="tagline" type="text" name="tagline" value="{{ old('tagline', $settings?->tagline) }}" maxlength="255" placeholder="Care for every chapter">
              @error('tagline')<span class="website-settings-error">{{ $message }}</span>@enderror
            </div>
            <div class="website-settings-field">
              <label for="header-logo">Header logo <span>(optional, JPG/PNG/WebP, max 2 MB)</span></label>
              <input class="website-settings-file" id="header-logo" type="file" name="header_logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
              @error('header_logo')<span class="website-settings-error">{{ $message }}</span>@enderror
              @if ($settings?->header_logo_url)
                <div class="website-logo-preview"><span>Current header logo</span><img src="{{ $settings->header_logo_url }}" alt="Current header logo"></div>
              @endif
            </div>
            <div class="website-settings-field">
              <label for="footer-logo">Footer logo <span>(optional, JPG/PNG/WebP, max 2 MB)</span></label>
              <input class="website-settings-file" id="footer-logo" type="file" name="footer_logo" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
              @error('footer_logo')<span class="website-settings-error">{{ $message }}</span>@enderror
              @if ($settings?->footer_logo_url)
                <div class="website-logo-preview"><span>Current footer logo</span><img src="{{ $settings->footer_logo_url }}" alt="Current footer logo"></div>
              @endif
            </div>
          </div>
        </section>

        <section class="panel website-settings-panel" aria-labelledby="contact-settings-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="contact-settings-title">Contact details</h2>
              <p class="panel-caption">Add the ways patients and visitors can reach you.</p>
            </div>
          </div>
          <div class="website-settings-grid">
            <div class="website-settings-field">
              <label for="contact-email">Contact email</label>
              <input id="contact-email" type="email" name="contact_email" value="{{ old('contact_email', $settings?->contact_email) }}" maxlength="255" placeholder="care@example.com" autocomplete="email">
              @error('contact_email')<span class="website-settings-error">{{ $message }}</span>@enderror
            </div>
            <div class="website-settings-field">
              <label for="phone">Phone number</label>
              <input id="phone" type="tel" name="phone" value="{{ old('phone', $settings?->phone) }}" maxlength="30" placeholder="+91 98765 43210" autocomplete="tel">
              @error('phone')<span class="website-settings-error">{{ $message }}</span>@enderror
            </div>
            <div class="website-settings-field">
              <label for="whatsapp">WhatsApp number <span>(optional)</span></label>
              <input id="whatsapp" type="tel" name="whatsapp" value="{{ old('whatsapp', $settings?->whatsapp) }}" maxlength="30" placeholder="+91 98765 43210">
              @error('whatsapp')<span class="website-settings-error">{{ $message }}</span>@enderror
            </div>
            <div class="website-settings-field">
              <label for="website-url">Website URL <span>(optional)</span></label>
              <input id="website-url" type="url" name="website_url" value="{{ old('website_url', $settings?->website_url) }}" maxlength="2048" placeholder="https://example.com" autocomplete="url">
              @error('website_url')<span class="website-settings-error">{{ $message }}</span>@enderror
            </div>
            <div class="website-settings-field website-settings-field-wide">
              <label for="address">Address <span>(optional)</span></label>
              <textarea id="address" name="address" rows="3" maxlength="2000" placeholder="Street, area, city, postal code">{{ old('address', $settings?->address) }}</textarea>
              @error('address')<span class="website-settings-error">{{ $message }}</span>@enderror
            </div>
          </div>
        </section>

        <section class="panel website-settings-panel" aria-labelledby="footer-settings-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="footer-settings-title">Footer details</h2>
              <p class="panel-caption">Customize the short message and copyright line shown in your website footer.</p>
            </div>
          </div>
          <div class="website-settings-grid">
            <div class="website-settings-field website-settings-field-wide">
              <label for="footer-text">Footer text <span>(optional)</span></label>
              <textarea id="footer-text" name="footer_text" rows="3" maxlength="2000" placeholder="A short introduction or message for your website footer...">{{ old('footer_text', $settings?->footer_text) }}</textarea>
              @error('footer_text')<span class="website-settings-error">{{ $message }}</span>@enderror
            </div>
            <div class="website-settings-field website-settings-field-wide">
              <label for="copyright-text">Copyright text <span>(optional)</span></label>
              <input id="copyright-text" type="text" name="copyright_text" value="{{ old('copyright_text', $settings?->copyright_text) }}" maxlength="255" placeholder="© {{ date('Y') }} {{ $settings?->site_name ?? $user->name }}. All rights reserved.">
              @error('copyright_text')<span class="website-settings-error">{{ $message }}</span>@enderror
            </div>
          </div>
        </section>

        <div class="website-settings-save-row">
          <span>Logo uploads are saved in your own public account folder.</span>
          <button class="website-settings-save" type="submit"><i class="fas fa-save"></i> Save website settings</button>
        </div>
      </form>
    </div>
  </div>
</main>

<style>
  .website-settings-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .website-settings-alert.error { color: #8b5147; background: #fbefec; border-color: #efd7d1; }
  .website-settings-form { display: grid; gap: 18px; }
  .website-settings-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .website-settings-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; padding: 0 20px 20px; }
  .website-settings-field { min-width: 0; display: grid; align-content: start; gap: 7px; }
  .website-settings-field-wide { grid-column: 1 / -1; }
  .website-settings-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .website-settings-field label span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .website-settings-field input:not([type=file]), .website-settings-field textarea { width: 100%; min-height: 40px; padding: 10px 11px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .website-settings-field input:focus, .website-settings-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .website-settings-field textarea { resize: vertical; line-height: 1.6; }
  .website-settings-file { width: 100%; padding: 9px; border: 1px solid #e2e5dc; border-radius: 8px; font: inherit; font-size: 11px; }
  .website-settings-error { color: #a34d40; font-size: 11px; }
  .website-logo-preview { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px; background: #f8f9f5; border: 1px solid var(--line); border-radius: 8px; }
  .website-logo-preview span { color: var(--muted); font-size: 10px; }
  .website-logo-preview img { max-width: 180px; max-height: 70px; object-fit: contain; }
  .website-settings-save-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 15px 18px; background: #fff; border: 1px solid var(--line); border-radius: 11px; box-shadow: var(--shadow); }
  .website-settings-save-row > span { color: var(--muted); font-size: 11px; }
  .website-settings-save { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 16px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .website-settings-save:hover { background: var(--green); }
  @media (max-width: 650px) { .website-settings-grid { grid-template-columns: 1fr; } .website-settings-field-wide { grid-column: auto; } .website-settings-save-row { align-items: stretch; flex-direction: column; } .website-settings-save { width: 100%; } }
</style>
@endsection
