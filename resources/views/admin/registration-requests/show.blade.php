@extends('layout.app')

@section('title', 'Registration Request Details')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Registration requests</strong><span> / </span>Request #{{ $registrationRequest->id }}</div>
    </div>
    <div class="topbar-actions">
      <div class="admin-profile">
        <div class="avatar" aria-hidden="true">AD</div>
        <div class="profile-copy"><strong>Administrator</strong><span>Clinic manager</span></div>
      </div>
    </div>
  </header>

  <div class="content-scroll">
    <div class="content-wrap">
      <a class="registration-back" href="{{ route('admin.registration-requests.index') }}"><i class="fas fa-arrow-left"></i> All registration requests</a>

      @if (session('status'))
        <div class="category-alert" role="status">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="category-alert error" role="alert">{{ $errors->first() }}</div>
      @endif

      <section class="page-heading" aria-labelledby="registration-detail-title">
        <div>
          <p class="eyebrow">Registration request #{{ $registrationRequest->id }}</p>
          <h1 id="registration-detail-title">{{ $registrationRequest->practice_name }}</h1>
          <p class="page-subtitle">{{ $planLabel }} · Received {{ $registrationRequest->created_at->format('M j, Y \a\t g:i A') }}</p>
        </div>
        <span class="registration-status {{ $registrationRequest->status }}">{{ ucfirst($registrationRequest->status) }}</span>
      </section>

      <div class="registration-detail-grid">
        <section class="panel registration-detail-panel">
          <div class="panel-heading">
            <div><h2 class="panel-title">Applicant and practice details</h2><p class="panel-caption">Information submitted with this request</p></div>
          </div>
          <dl class="registration-details">
            <div><dt>Practice type</dt><dd>{{ ucfirst($registrationRequest->practice_type) }}</dd></div>
            <div><dt>Practice / organization</dt><dd>{{ $registrationRequest->practice_name }}</dd></div>
            <div><dt>Specialty / main service</dt><dd>{{ $registrationRequest->specialty ?: 'Not provided' }}</dd></div>
            <div>
              <dt>Website</dt>
              <dd>
                @if ($registrationRequest->website_url)
                  <a href="{{ $registrationRequest->website_url }}" target="_blank" rel="noopener noreferrer">{{ $registrationRequest->website_url }}</a>
                @else
                  Not provided
                @endif
              </dd>
            </div>
            <div><dt>Address</dt><dd>{!! nl2br(e($registrationRequest->address)) !!}<br>{{ $registrationRequest->city }}, {{ $registrationRequest->state }} {{ $registrationRequest->postal_code }}</dd></div>
            <div><dt>Additional message</dt><dd class="registration-message">{{ $registrationRequest->message ?: 'No additional message' }}</dd></div>
            <div><dt>Contact consent recorded</dt><dd>{{ $registrationRequest->consented_at?->format('M j, Y \a\t g:i A') ?? 'Not available' }}</dd></div>
          </dl>
        </section>

        <aside class="registration-side-column">
          <section class="panel registration-detail-panel">
            <div class="panel-heading">
              <div><h2 class="panel-title">Contact applicant</h2><p class="panel-caption">Reach out using the submitted contact details.</p></div>
            </div>
            <div class="registration-contact-details">
              <div><span>Contact person</span><strong>{{ $registrationRequest->contact_name }}</strong></div>
              <div><span>Email</span><a href="mailto:{{ $registrationRequest->email }}">{{ $registrationRequest->email }}</a></div>
              <div><span>Phone</span><a href="tel:{{ $registrationRequest->phone }}">{{ $registrationRequest->phone }}</a></div>
            </div>
            <div class="registration-contact-actions">
              <a href="mailto:{{ $registrationRequest->email }}?subject={{ rawurlencode('MediCare registration request #'.$registrationRequest->id) }}"><i class="fas fa-envelope"></i> Email</a>
              <a href="tel:{{ $registrationRequest->phone }}"><i class="fas fa-phone-alt"></i> Call</a>
              @php($whatsappNumber = preg_replace('/\D+/', '', $registrationRequest->phone))
              @if ($whatsappNumber !== '')
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp"></i> WhatsApp</a>
              @endif
            </div>
          </section>

          <section class="panel registration-detail-panel">
            <div class="panel-heading">
              <div><h2 class="panel-title">Update request status</h2><p class="panel-caption">Track your follow-up with this applicant.</p></div>
            </div>
            <form class="registration-status-form" method="POST" action="{{ route('admin.registration-requests.status', $registrationRequest) }}">
              @csrf
              @method('PATCH')
              <label for="request-status">Status</label>
              <select id="request-status" name="status" required>
                @foreach ($statuses as $key => $label)
                  <option value="{{ $key }}" @selected($registrationRequest->status === $key)>{{ $label }}</option>
                @endforeach
              </select>
              <button type="submit">Save status <i class="fas fa-check"></i></button>
            </form>
          </section>
        </aside>
      </div>
    </div>
  </div>
</main>

<style>
  .registration-back { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 19px; color: var(--green); font-size: 11px; font-weight: 700; }
  .registration-detail-grid { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(280px, .8fr); gap: 16px; align-items: start; }
  .registration-detail-panel { overflow: hidden; margin-bottom: 15px; }
  .registration-details { margin: 0; padding: 0 20px 10px; }
  .registration-details > div { display: grid; grid-template-columns: minmax(140px, .65fr) minmax(0, 1.35fr); gap: 15px; padding: 12px 0; border-top: 1px solid #f0efe9; }
  .registration-details dt, .registration-contact-details span { color: #899188; font-size: 10px; }
  .registration-details dd { margin: 0; color: #3c4b40; font-size: 11px; line-height: 1.65; overflow-wrap: anywhere; }
  .registration-details dd a, .registration-contact-details a { color: var(--green); font-weight: 600; overflow-wrap: anywhere; }
  .registration-message { white-space: pre-wrap; }
  .registration-contact-details { display: grid; gap: 14px; padding: 0 20px 18px; }
  .registration-contact-details span, .registration-contact-details strong, .registration-contact-details a { display: block; }
  .registration-contact-details span { margin-bottom: 5px; }
  .registration-contact-details strong, .registration-contact-details a { color: #3c4b40; font-size: 11px; }
  .registration-contact-actions { display: flex; flex-wrap: wrap; gap: 8px; padding: 14px 20px 18px; border-top: 1px solid #f0efe9; }
  .registration-contact-actions a { display: inline-flex; align-items: center; gap: 7px; min-height: 34px; padding: 0 11px; color: var(--green-dark); background: #edf2ea; border-radius: 6px; font-size: 10px; font-weight: 700; }
  .registration-status-form { display: grid; gap: 9px; padding: 0 20px 20px; }
  .registration-status-form label { color: #899188; font-size: 10px; }
  .registration-status-form select { min-height: 39px; padding: 8px 10px; color: var(--ink); background: white; border: 1px solid var(--line); border-radius: 6px; font: inherit; font-size: 11px; }
  .registration-status-form button { min-height: 39px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; color: white; background: var(--green-dark); border: 0; border-radius: 6px; cursor: pointer; font: inherit; font-size: 10px; font-weight: 700; }
  @media (max-width: 850px) { .registration-detail-grid { grid-template-columns: 1fr; } }
  @media (max-width: 560px) { .registration-details > div { grid-template-columns: 1fr; gap: 5px; } }
</style>
@endsection
