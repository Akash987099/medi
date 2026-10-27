@extends('layout.app')

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
    <section class="page-heading" aria-labelledby="page-title">
      <div>
        <p class="eyebrow">Your day, thoughtfully planned</p>
        <h1 id="page-title">Today at the clinic</h1>
        <p class="page-subtitle">A thoughtful overview of the people and moments in your care today.</p>
      </div>
      <div class="date-chip"><i class="far fa-calendar"></i>{{ now()->format('l, F j, Y') }}</div>
    </section>

    <section class="welcome-banner" aria-label="Daily welcome">
      <div class="welcome-copy">
        <p class="eyebrow">A note for today</p>
        <h2>Care begins with feeling welcome.</h2>
        <p>Every appointment is a chance to make someone’s day feel a little easier. Your team is ready for a lovely day of care.</p>
      </div>
      <a class="banner-action" href="#appointments">View today’s schedule <i class="fas fa-arrow-right"></i></a>
      <div class="banner-art" aria-hidden="true"></div>
    </section>

    <section class="stats-grid" id="patient-flow" aria-label="Today's clinic summary">
      <article class="stat-card">
        <div class="stat-top"><span class="stat-label">Appointments</span><span class="stat-icon"><i class="far fa-calendar-check"></i></span></div>
        <div class="stat-value"><strong>24</strong><span class="stat-trend"><i class="fas fa-arrow-up"></i> 12%</span></div>
        <p class="stat-foot">scheduled for today</p>
      </article>
      <article class="stat-card">
        <div class="stat-top"><span class="stat-label">Guests checked in</span><span class="stat-icon clay"><i class="fas fa-door-open"></i></span></div>
        <div class="stat-value"><strong>08</strong><span class="stat-trend">of 24</span></div>
        <p class="stat-foot">the morning is off to a good start</p>
      </article>
      <article class="stat-card" id="care-team">
        <div class="stat-top"><span class="stat-label">Care team on duty</span><span class="stat-icon blue"><i class="fas fa-user-md"></i></span></div>
        <div class="stat-value"><strong>12</strong><span class="stat-trend">across 4 services</span></div>
        <p class="stat-foot">all departments covered</p>
      </article>
      <article class="stat-card">
        <div class="stat-top"><span class="stat-label">Follow-ups due</span><span class="stat-icon gold"><i class="far fa-heart"></i></span></div>
        <div class="stat-value"><strong>06</strong><span class="stat-trend">to make today</span></div>
        <p class="stat-foot">a small check-in goes a long way</p>
      </article>
    </section>

    <div class="dashboard-grid">
      <section class="panel" id="appointments" aria-labelledby="schedule-heading">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title" id="schedule-heading">Today’s appointments</h2>
            <p class="panel-caption">A few of the next guests expected</p>
          </div>
          <a class="text-link" href="#appointments">Full schedule <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="schedule-list">
          <article class="schedule-row">
            <time class="schedule-time" datetime="09:00">09:00</time>
            <div class="patient-info">
              <span class="patient-avatar">AM</span>
              <span class="patient-copy"><strong>Amelia Morgan</strong><span>Cardiology consultation</span></span>
            </div>
            <span class="status checked">Checked in</span>
          </article>
          <article class="schedule-row">
            <time class="schedule-time" datetime="09:30">09:30</time>
            <div class="patient-info">
              <span class="patient-avatar peach">JT</span>
              <span class="patient-copy"><strong>James Taylor</strong><span>ECG · Follow-up</span></span>
            </div>
            <span class="status waiting">Arriving</span>
          </article>
          <article class="schedule-row">
            <time class="schedule-time" datetime="10:15">10:15</time>
            <div class="patient-info">
              <span class="patient-avatar blue">SK</span>
              <span class="patient-copy"><strong>Sofia Khan</strong><span>Echocardiogram</span></span>
            </div>
            <span class="status upcoming">Upcoming</span>
          </article>
          <article class="schedule-row">
            <time class="schedule-time" datetime="10:45">10:45</time>
            <div class="patient-info">
              <span class="patient-avatar yellow">DL</span>
              <span class="patient-copy"><strong>Daniel Lewis</strong><span>Blood pressure review</span></span>
            </div>
            <span class="status upcoming">Upcoming</span>
          </article>
        </div>
      </section>

      <aside class="panel guest-card" aria-labelledby="experience-heading">
        <div class="panel-heading">
          <div>
            <h2 class="panel-title" id="experience-heading">The welcome report</h2>
            <p class="panel-caption">A little care goes a long way</p>
          </div>
          <span class="stat-icon gold"><i class="fas fa-sparkles"></i></span>
        </div>
        <p class="guest-intro">Guests are noticing the thoughtful touches your team brings to every visit.</p>
        <div class="guest-score">
          <div class="score-ring" role="img" aria-label="4.8 out of 5 guest experience rating"><strong>4.8</strong></div>
          <div class="score-copy"><strong>Wonderful work</strong><span>Guest experience<br>this month</span></div>
        </div>
        <div class="guest-divider"></div>
        <div class="guest-foot"><span>Recent guest feedback</span><strong>View notes <i class="fas fa-arrow-right"></i></strong></div>
      </aside>
    </div>

    <section class="panel services-panel" id="services" aria-labelledby="services-heading">
      <div class="panel-heading">
        <div>
          <h2 class="panel-title" id="services-heading">Care, thoughtfully delivered</h2>
          <p class="panel-caption">Today’s most active services</p>
        </div>
        <a class="text-link" href="#services">All services <i class="fas fa-arrow-right"></i></a>
      </div>
      <div class="service-grid">
        <article class="service-item">
          <span class="service-icon"><i class="fas fa-heartbeat"></i></span>
          <span class="service-copy"><strong>Cardiology</strong><span>9 visits today</span></span>
        </article>
        <article class="service-item">
          <span class="service-icon clay"><i class="fas fa-wave-square"></i></span>
          <span class="service-copy"><strong>Diagnostics</strong><span>8 tests today</span></span>
        </article>
        <article class="service-item">
          <span class="service-icon gold"><i class="fas fa-hand-holding-heart"></i></span>
          <span class="service-copy"><strong>Follow-up care</strong><span>7 visits today</span></span>
        </article>
      </div>
    </section>

      <p class="footer-note"><i class="fas fa-leaf"></i> Thoughtful care, one visit at a time.</p>
    </div>
  </div>
</main>
@endsection
