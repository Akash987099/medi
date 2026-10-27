@extends('users.layout.app')

@section('title', 'SMTP Setup')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>SMTP Setup</div>
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
      <section class="page-heading" aria-labelledby="smtp-title">
        <div>
          <p class="eyebrow">Private email delivery settings</p>
          <h1 id="smtp-title">SMTP Setup</h1>
          <p class="page-subtitle">Configure your own outgoing mail server. These settings are private to your account and will not change other users’ or application-wide mail settings.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="smtp-alert success" role="status">{{ session('status') }}</div>
      @endif
      @if ($errors->has('smtp'))
        <div class="smtp-alert error" role="alert">{{ $errors->first('smtp') }}</div>
      @endif

      <div class="smtp-layout">
        <section class="panel smtp-settings-panel" aria-labelledby="smtp-server-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="smtp-server-title">Mail server</h2>
              <p class="panel-caption">SMTP credentials are stored encrypted. The password is never shown after saving.</p>
            </div>
            <span class="smtp-saved-badge {{ $settings ? 'configured' : '' }}">
              <i class="fas {{ $settings ? 'fa-check-circle' : 'fa-circle' }}"></i>
              {{ $settings ? 'Configured' : 'Not configured' }}
            </span>
          </div>
          <form class="smtp-form" method="POST" action="{{ route('user.smtp.save') }}">
            @csrf
            @method('PUT')
            <div class="smtp-fields">
              <div class="smtp-field smtp-field-wide">
                <label for="smtp-host">SMTP host</label>
                <input id="smtp-host" type="text" name="host" value="{{ old('host', $settings?->host) }}" maxlength="255" placeholder="smtp.example.com" autocomplete="url" required>
                @error('host')<span class="smtp-error">{{ $message }}</span>@enderror
              </div>
              <div class="smtp-field">
                <label for="smtp-port">Port</label>
                <input id="smtp-port" type="number" name="port" value="{{ old('port', $settings?->port ?? 587) }}" min="1" max="65535" required>
                @error('port')<span class="smtp-error">{{ $message }}</span>@enderror
              </div>
              <div class="smtp-field">
                <label for="smtp-encryption">Encryption</label>
                <select id="smtp-encryption" name="encryption" required>
                  <option value="tls" @selected(old('encryption', $settings?->encryption ?? 'tls') === 'tls')>STARTTLS (recommended)</option>
                  <option value="ssl" @selected(old('encryption', $settings?->encryption ?? '') === 'ssl')>SSL/TLS</option>
                  <option value="none" @selected(old('encryption', $settings?->encryption ?? '') === 'none')>Auto / server default</option>
                </select>
                @error('encryption')<span class="smtp-error">{{ $message }}</span>@enderror
              </div>
              <div class="smtp-field">
                <label for="smtp-username">Username <span>(if required)</span></label>
                <input id="smtp-username" type="text" name="username" value="{{ old('username', $settings?->username) }}" maxlength="255" autocomplete="username">
                @error('username')<span class="smtp-error">{{ $message }}</span>@enderror
              </div>
              <div class="smtp-field">
                <label for="smtp-password">Password <span>(leave blank to keep the saved password)</span></label>
                <input id="smtp-password" type="password" name="password" maxlength="1024" autocomplete="new-password" placeholder="{{ $settings?->password ? 'Saved securely — enter a new password to replace it' : 'SMTP password or app password' }}">
                @error('password')<span class="smtp-error">{{ $message }}</span>@enderror
              </div>
              <div class="smtp-field">
                <label for="smtp-from-email">From email</label>
                <input id="smtp-from-email" type="email" name="from_email" value="{{ old('from_email', $settings?->from_email) }}" maxlength="255" placeholder="care@example.com" autocomplete="email" required>
                @error('from_email')<span class="smtp-error">{{ $message }}</span>@enderror
              </div>
              <div class="smtp-field">
                <label for="smtp-from-name">From name</label>
                <input id="smtp-from-name" type="text" name="from_name" value="{{ old('from_name', $settings?->from_name ?? $user->name) }}" maxlength="255" placeholder="{{ $user->name }}" required>
                @error('from_name')<span class="smtp-error">{{ $message }}</span>@enderror
              </div>
            </div>
            <div class="smtp-security-note">
              <i class="fas fa-lock" aria-hidden="true"></i>
              <span>SMTP credentials are isolated per account. Saving these values does not modify the application’s global mail configuration.</span>
            </div>
            <button class="smtp-save-button" type="submit"><i class="fas fa-save"></i> Save SMTP settings</button>
          </form>
        </section>

        <section class="panel smtp-test-panel" aria-labelledby="smtp-test-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="smtp-test-title">Send a test email</h2>
              <p class="panel-caption">Save your settings first, then send a test message to an address you control.</p>
            </div>
          </div>
          <form class="smtp-test-form" method="POST" action="{{ route('user.smtp.test') }}">
            @csrf
            <div class="smtp-field">
              <label for="smtp-recipient">Test recipient email</label>
              <input id="smtp-recipient" type="email" name="recipient" value="{{ old('recipient', $user->email) }}" maxlength="255" placeholder="you@example.com" autocomplete="email" required>
              @error('recipient')<span class="smtp-error">{{ $message }}</span>@enderror
            </div>
            <button class="smtp-test-button" type="submit" @disabled(!$settings)>
              <i class="far fa-paper-plane"></i> Send test email
            </button>
            @if (!$settings)
              <p class="smtp-test-hint">Save your SMTP configuration to enable the test email.</p>
            @endif
          </form>
        </section>
      </div>
    </div>
  </div>
</main>

<style>
  .smtp-alert { margin-bottom: 18px; padding: 12px 15px; border: 1px solid; border-radius: 9px; font-size: 13px; }
  .smtp-alert.success { color: #365844; background: #e8f1e6; border-color: #d5e6d2; }
  .smtp-alert.error { color: #8b5147; background: #fbefec; border-color: #efd7d1; }
  .smtp-layout { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(270px, .7fr); align-items: start; gap: 20px; }
  .smtp-settings-panel, .smtp-test-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .smtp-settings-panel .panel-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
  .smtp-saved-badge { flex: 0 0 auto; display: inline-flex; align-items: center; gap: 6px; padding: 7px 10px; color: #8b6557; background: #f5ece7; border-radius: 20px; font-size: 10px; font-weight: 700; }
  .smtp-saved-badge.configured { color: #365844; background: #e8f1e6; }
  .smtp-form, .smtp-test-form { display: grid; gap: 17px; padding: 0 20px 21px; }
  .smtp-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
  .smtp-field { min-width: 0; display: grid; align-content: start; gap: 7px; }
  .smtp-field-wide { grid-column: 1 / -1; }
  .smtp-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .smtp-field label span { color: var(--muted); font-size: 10px; font-weight: 400; }
  .smtp-field input, .smtp-field select { width: 100%; min-height: 40px; padding: 10px 11px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .smtp-field input:focus, .smtp-field select:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .smtp-error { color: #a34d40; font-size: 11px; }
  .smtp-security-note { display: flex; align-items: flex-start; gap: 9px; padding: 12px; color: #536658; background: #f4f7f1; border: 1px solid #e2e9df; border-radius: 8px; font-size: 10px; line-height: 1.6; }
  .smtp-security-note i { margin-top: 2px; color: var(--green); }
  .smtp-save-button, .smtp-test-button { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .smtp-save-button:hover, .smtp-test-button:hover:not(:disabled) { background: var(--green); }
  .smtp-test-button:disabled { cursor: not-allowed; opacity: .55; }
  .smtp-test-hint { margin: -8px 0 0; color: var(--muted); font-size: 10px; line-height: 1.5; }
  @media (max-width: 900px) { .smtp-layout { grid-template-columns: 1fr; } }
  @media (max-width: 560px) { .smtp-fields { grid-template-columns: 1fr; } .smtp-field-wide { grid-column: auto; } .smtp-settings-panel .panel-heading { flex-direction: column; } .smtp-save-button, .smtp-test-button { width: 100%; } }
</style>
@endsection
