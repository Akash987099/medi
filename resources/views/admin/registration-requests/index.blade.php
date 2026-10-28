@extends('layout.app')

@section('title', 'Registration Requests')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Registration requests</div>
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
      <section class="page-heading" aria-labelledby="registration-requests-title">
        <div>
          <p class="eyebrow">Care partner enquiries</p>
          <h1 id="registration-requests-title">Registration requests</h1>
          <p class="page-subtitle">Review plan enquiries, contact applicants, and track each request through to a decision.</p>
        </div>
      </section>

      <div class="registration-summary">
        <article class="registration-summary-card"><span>All requests</span><strong>{{ $counts->sum() }}</strong></article>
        @foreach ($statuses as $key => $label)
          <article class="registration-summary-card"><span>{{ $label }}</span><strong>{{ $counts[$key] ?? 0 }}</strong></article>
        @endforeach
      </div>

      <section class="panel registration-list-panel" aria-label="Registration requests list">
        <div class="registration-list-toolbar">
          <div>
            <h2 class="panel-title">Applicants</h2>
            <p class="panel-caption">{{ $requests->total() }} {{ \Illuminate\Support\Str::plural('request', $requests->total()) }} found</p>
          </div>
          <form class="registration-filter" method="GET" action="{{ route('admin.registration-requests.index') }}">
            <label class="sr-only" for="registration-search">Search registration requests</label>
            <input id="registration-search" type="search" name="q" value="{{ $filters['q'] }}" maxlength="120" placeholder="Practice, contact, email or phone">
            <label class="sr-only" for="registration-status">Filter by status</label>
            <select id="registration-status" name="status">
              <option value="all" @selected($filters['status'] === 'all')>All statuses</option>
              @foreach ($statuses as $key => $label)
                <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
              @endforeach
            </select>
            <button type="submit" class="registration-filter-button"><i class="fas fa-search"></i> Find</button>
            @if ($filters['q'] !== '' || $filters['status'] !== 'all')
              <a class="registration-clear" href="{{ route('admin.registration-requests.index') }}">Clear</a>
            @endif
          </form>
        </div>

        <div class="category-table-wrap">
          <table class="category-table">
            <thead>
              <tr>
                <th scope="col">Practice / contact</th>
                <th scope="col">Interested in</th>
                <th scope="col">Location</th>
                <th scope="col">Status</th>
                <th scope="col">Received</th>
                <th scope="col">Details</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($requests as $registrationRequest)
                <tr>
                  <td>
                    <strong>{{ $registrationRequest->practice_name }}</strong>
                    <span class="registration-cell-sub">{{ $registrationRequest->contact_name }}</span>
                    <a class="registration-contact-link" href="mailto:{{ $registrationRequest->email }}">{{ $registrationRequest->email }}</a>
                  </td>
                  <td>{{ $plans[$registrationRequest->plan] ?? $registrationRequest->plan }}</td>
                  <td>{{ $registrationRequest->city }}, {{ $registrationRequest->state }}</td>
                  <td><span class="registration-status {{ $registrationRequest->status }}">{{ ucfirst($registrationRequest->status) }}</span></td>
                  <td class="category-date">{{ $registrationRequest->created_at->format('M j, Y') }}</td>
                  <td><a class="registration-view-link" href="{{ route('admin.registration-requests.show', $registrationRequest) }}">View details <i class="fas fa-arrow-right"></i></a></td>
                </tr>
              @empty
                <tr><td class="registration-empty" colspan="6">No registration requests found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($requests->hasPages())
          <div class="registration-pagination">{{ $requests->links() }}</div>
        @endif
      </section>
    </div>
  </div>
</main>

<style>
  .registration-summary { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
  .registration-summary-card { min-height: 82px; display: flex; flex-direction: column; justify-content: space-between; padding: 14px 16px; background: var(--white); border: 1px solid var(--line); border-radius: 9px; }
  .registration-summary-card span { color: var(--muted); font-size: 10px; }
  .registration-summary-card strong { color: var(--green-dark); font-family: 'Playfair Display', serif; font-size: 24px; }
  .registration-list-panel { overflow: hidden; }
  .registration-list-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 20px; }
  .registration-list-toolbar .panel-title { margin-bottom: 4px; }
  .registration-filter { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
  .registration-filter input, .registration-filter select { min-height: 38px; padding: 8px 10px; color: var(--ink); background: white; border: 1px solid var(--line); border-radius: 6px; font: inherit; font-size: 10px; }
  .registration-filter input { width: min(250px, 30vw); }
  .registration-filter-button, .registration-view-link { display: inline-flex; align-items: center; justify-content: center; gap: 7px; color: var(--green-dark); font-size: 10px; font-weight: 700; }
  .registration-filter-button { min-height: 38px; padding: 0 12px; background: #edf2ea; border: 1px solid #dfe7dc; border-radius: 6px; cursor: pointer; }
  .registration-clear { color: var(--muted); font-size: 10px; }
  .registration-cell-sub, .registration-contact-link { display: block; margin-top: 4px; color: var(--muted); font-size: 10px; }
  .registration-status { display: inline-flex; padding: 5px 9px; border-radius: 20px; font-size: 9px; font-weight: 700; white-space: nowrap; }
  .registration-status.pending { color: #8b6932; background: #f8f1e3; }
  .registration-status.contacted { color: #526d83; background: #eaf0f4; }
  .registration-status.approved { color: #47704f; background: #edf4eb; }
  .registration-status.rejected { color: #944c44; background: #f8ecea; }
  .registration-empty { padding: 35px !important; color: var(--muted); text-align: center; }
  .registration-pagination { padding: 16px 20px; }
  @media (max-width: 900px) { .registration-list-toolbar { align-items: flex-start; flex-direction: column; } .registration-summary { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
  @media (max-width: 560px) { .registration-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); } .registration-filter { width: 100%; } .registration-filter input { width: 100%; } .registration-filter select { flex: 1; } }
</style>
@endsection
