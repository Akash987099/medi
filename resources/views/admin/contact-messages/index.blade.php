@extends('layout.app')

@section('title', 'Contact Messages')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Contact messages</div>
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
      <section class="page-heading" aria-labelledby="contact-messages-title">
        <div>
          <p class="eyebrow">Website enquiries</p>
          <h1 id="contact-messages-title">Contact messages</h1>
          <p class="page-subtitle">Review website enquiries and follow up with people who contacted MediCare.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="registration-alert" role="status">{{ session('status') }}</div>
      @endif

      <div class="registration-summary">
        <article class="registration-summary-card"><span>All messages</span><strong>{{ $counts->sum() }}</strong></article>
        @foreach ($statuses as $status)
          <article class="registration-summary-card"><span>{{ \Illuminate\Support\Str::headline($status) }}</span><strong>{{ $counts[$status] ?? 0 }}</strong></article>
        @endforeach
      </div>

      <section class="panel registration-list-panel" aria-label="Website contact messages">
        <div class="registration-list-toolbar">
          <div>
            <h2 class="panel-title">Inbox</h2>
            <p class="panel-caption">{{ $messages->total() }} {{ \Illuminate\Support\Str::plural('message', $messages->total()) }} found</p>
          </div>
          <form class="registration-filter" method="GET" action="{{ route('admin.contact-messages.index') }}">
            <label class="sr-only" for="contact-message-search">Search contact messages</label>
            <input id="contact-message-search" type="search" name="q" value="{{ $filters['q'] }}" maxlength="120" placeholder="Name, email, subject or message">
            <label class="sr-only" for="contact-message-status">Filter by status</label>
            <select id="contact-message-status" name="status">
              <option value="all" @selected($filters['status'] === 'all')>All statuses</option>
              @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
              @endforeach
            </select>
            <button type="submit" class="registration-filter-button"><i class="fas fa-search"></i> Find</button>
            @if ($filters['q'] !== '' || $filters['status'] !== 'all')
              <a class="registration-clear" href="{{ route('admin.contact-messages.index') }}">Clear</a>
            @endif
          </form>
        </div>

        <div class="category-table-wrap">
          <table class="category-table">
            <thead>
              <tr>
                <th scope="col">Sender</th>
                <th scope="col">Subject and message</th>
                <th scope="col">Status</th>
                <th scope="col">Received</th>
                <th scope="col">Update status</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($messages as $contactMessage)
                <tr>
                  <td>
                    <strong>{{ $contactMessage->name }}</strong>
                    <a class="registration-contact-link" href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a>
                    @if ($contactMessage->phone)
                      <a class="registration-contact-link" href="tel:{{ $contactMessage->phone }}">{{ $contactMessage->phone }}</a>
                    @endif
                  </td>
                  <td class="contact-message-copy"><strong>{{ $contactMessage->subject }}</strong><p>{{ $contactMessage->message }}</p></td>
                  <td><span class="registration-status {{ $contactMessage->status }}">{{ \Illuminate\Support\Str::headline($contactMessage->status) }}</span></td>
                  <td class="category-date">{{ $contactMessage->created_at->format('M j, Y, g:i A') }}</td>
                  <td>
                    <form class="contact-status-form" method="POST" action="{{ route('admin.contact-messages.status', $contactMessage) }}">
                      @csrf
                      @method('PATCH')
                      <label class="sr-only" for="contact-status-{{ $contactMessage->id }}">Update status for {{ $contactMessage->name }}</label>
                      <select id="contact-status-{{ $contactMessage->id }}" name="status">
                        @foreach ($statuses as $status)
                          <option value="{{ $status }}" @selected($contactMessage->status === $status)>{{ \Illuminate\Support\Str::headline($status) }}</option>
                        @endforeach
                      </select>
                      <button type="submit" class="contact-status-button">Save</button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr><td class="registration-empty" colspan="5">No contact messages found.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
        @if ($messages->hasPages())
          <div class="registration-pagination">{{ $messages->links() }}</div>
        @endif
      </section>
    </div>
  </div>
</main>

<style>
  .registration-alert { margin-bottom: 15px; padding: 12px 14px; color: #4c6a50; background: #eff5ed; border: 1px solid #dce8d9; border-radius: 8px; font-size: 11px; }
  .registration-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 18px; }
  .registration-summary-card { min-height: 82px; display: flex; flex-direction: column; justify-content: space-between; padding: 14px 16px; background: var(--white); border: 1px solid var(--line); border-radius: 9px; }
  .registration-summary-card span { color: var(--muted); font-size: 10px; }
  .registration-summary-card strong { color: var(--green-dark); font-family: 'Playfair Display', serif; font-size: 24px; }
  .registration-list-panel { overflow: hidden; }
  .registration-list-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 18px 20px; }
  .registration-list-toolbar .panel-title { margin-bottom: 4px; }
  .registration-filter, .contact-status-form { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
  .registration-filter input, .registration-filter select, .contact-status-form select { min-height: 38px; padding: 8px 10px; color: var(--ink); background: white; border: 1px solid var(--line); border-radius: 6px; font: inherit; font-size: 10px; }
  .registration-filter input { width: min(250px, 30vw); }
  .registration-filter-button, .contact-status-button { min-height: 36px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 0 12px; color: var(--green-dark); background: #edf2ea; border: 1px solid #dfe7dc; border-radius: 6px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .registration-clear { color: var(--muted); font-size: 10px; }
  .registration-contact-link { display: block; margin-top: 4px; color: var(--muted); font-size: 10px; }
  .registration-status { display: inline-flex; padding: 5px 9px; border-radius: 20px; font-size: 9px; font-weight: 700; white-space: nowrap; }
  .registration-status.new { color: #8b6932; background: #f8f1e3; }
  .registration-status.contacted { color: #526d83; background: #eaf0f4; }
  .registration-status.resolved { color: #47704f; background: #edf4eb; }
  .contact-message-copy { min-width: 260px; max-width: 520px; white-space: normal !important; }
  .contact-message-copy p { margin: 5px 0 0; color: #717c73; font-size: 10px; line-height: 1.6; white-space: pre-line; }
  .registration-empty { padding: 35px !important; color: var(--muted); text-align: center; }
  .registration-pagination { padding: 16px 20px; }
  @media (max-width: 900px) { .registration-list-toolbar { align-items: flex-start; flex-direction: column; } .registration-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
  @media (max-width: 560px) { .registration-filter { width: 100%; } .registration-filter input { width: 100%; } .registration-filter select { flex: 1; } }
</style>
@endsection
