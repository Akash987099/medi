@extends('users.layout.app')

@section('title', 'Overview')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Overview</div>
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
      <section class="page-heading" aria-labelledby="user-page-title">
        <div>
          <p class="eyebrow">Care partner workspace</p>
          <h1 id="user-page-title">Welcome, {{ $user->name }}</h1>
          <p class="page-subtitle">Your secure MediCare account and workspace at a glance.</p>
        </div>
        <div class="date-chip"><i class="far fa-calendar"></i>{{ now()->format('l, F j, Y') }}</div>
      </section>

      <section class="welcome-banner" aria-label="Workspace welcome">
        <div class="welcome-copy">
          <p class="eyebrow">Your care workspace</p>
          <h2>Your work makes a difference.</h2>
          <p>Thank you for being part of a connected care community. Your account is ready to use.</p>
        </div>
        <div class="banner-art" aria-hidden="true"></div>
      </section>

      <section class="stats-grid" aria-label="Account summary">
        <article class="stat-card">
          <div class="stat-top"><span class="stat-label">Account type</span><span class="stat-icon"><i class="fas {{ $user->role === 1 ? 'fa-hospital' : 'fa-user-md' }}"></i></span></div>
          <div class="stat-value"><strong>{{ $user->role === 1 ? 'Hospital' : 'Doctor' }}</strong></div>
          <p class="stat-foot">MediCare care partner</p>
        </article>
        <article class="stat-card">
          <div class="stat-top"><span class="stat-label">Account status</span><span class="stat-icon blue"><i class="fas fa-shield-alt"></i></span></div>
          <div class="stat-value"><strong>Active</strong></div>
          <p class="stat-foot">secure sign-in enabled</p>
        </article>
        <article class="stat-card">
          <div class="stat-top"><span class="stat-label">{{ $user->role === 1 ? 'Facility' : 'Specialty' }}</span><span class="stat-icon clay"><i class="{{ $user->role === 1 ? 'fas fa-clinic-medical' : 'fas fa-stethoscope' }}"></i></span></div>
          <div class="stat-value"><strong>{{ $user->role === 1 ? ($profile?->name ?? $user->name) : ($profile?->category?->name ?? 'Not assigned') }}</strong></div>
          <p class="stat-foot">{{ $user->role === 1 ? 'registered hospital' : 'registered specialty' }}</p>
        </article>
      </section>

      <section class="panel" id="profile-heading" aria-labelledby="profile-title">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title" id="profile-title">Account details</h2>
            <p class="panel-caption">Your registered contact and workspace information</p>
          </div>
        </div>
        <div class="schedule-list">
          <div class="schedule-row">
            <div class="schedule-time"><i class="far fa-user"></i></div>
            <div class="schedule-person"><span class="patient-avatar"><i class="fas {{ $user->role === 1 ? 'fa-hospital' : 'fa-user-md' }}"></i></span><span class="patient-copy"><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span></span></div>
            <span class="status confirmed">{{ $user->role === 1 ? 'Hospital' : 'Doctor' }}</span>
          </div>
          @if ($profile)
            <div class="schedule-row">
              <div class="schedule-time"><i class="fas fa-phone"></i></div>
              <div class="schedule-person"><span class="patient-avatar"><i class="fas fa-address-card"></i></span><span class="patient-copy"><strong>Contact number</strong><span>{{ $profile->phone }}</span></span></div>
              <span class="status confirmed">On file</span>
            </div>
            @if ($user->role === 2 && $profile->hospital)
              <div class="schedule-row">
                <div class="schedule-time"><i class="fas fa-hospital"></i></div>
                <div class="schedule-person"><span class="patient-avatar"><i class="fas fa-clinic-medical"></i></span><span class="patient-copy"><strong>Associated hospital</strong><span>{{ $profile->hospital->name }}</span></span></div>
                <span class="status confirmed">Linked</span>
              </div>
            @endif
          @endif
        </div>
      </section>

      <p class="footer-note"><i class="fas fa-leaf"></i> Thoughtful care, one visit at a time.</p>
    </div>
  </div>
</main>
@endsection
