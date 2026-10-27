@extends('users.layout.app')

@section('title', 'FAQs')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>FAQs</div>
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
      <section class="page-heading" aria-labelledby="faqs-title">
        <div>
          <p class="eyebrow">Helpful information</p>
          <h1 id="faqs-title">FAQs</h1>
          <p class="page-subtitle">Add answers to the questions patients ask most often.</p>
        </div>
      </section>

      @if (session('status'))
        <div class="faq-alert" role="status">{{ session('status') }}</div>
      @endif

      <div class="faq-layout">
        <section class="panel faq-form-panel" aria-labelledby="add-faq-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="add-faq-title">Add a FAQ</h2>
              <p class="panel-caption">Write a clear question and a helpful answer.</p>
            </div>
          </div>
          <form class="faq-form" method="POST" action="{{ route('user.faqs.store') }}">
            @csrf
            <div class="faq-field">
              <label for="question">Question</label>
              <input id="question" type="text" name="question" value="{{ old('question') }}" maxlength="250" placeholder="e.g. What are your clinic hours?" required>
              @error('question')<span class="faq-error">{{ $message }}</span>@enderror
            </div>
            <div class="faq-field">
              <label for="answer">Answer</label>
              <textarea id="answer" name="answer" rows="7" maxlength="5000" placeholder="Write the answer for your patients..." required>{{ old('answer') }}</textarea>
              @error('answer')<span class="faq-error">{{ $message }}</span>@enderror
            </div>
            <button class="faq-submit" type="submit"><i class="fas fa-plus"></i> Save FAQ</button>
          </form>
        </section>

        <section class="panel faq-list-panel" aria-labelledby="saved-faqs-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="saved-faqs-title">Saved FAQs</h2>
              <p class="panel-caption">{{ $faqs->total() }} {{ \Illuminate\Support\Str::plural('question', $faqs->total()) }} in your workspace</p>
            </div>
          </div>
          <form class="faq-filter" method="GET" action="{{ route('user.faqs.index') }}">
            <label class="faq-search-field" for="faq-search">
              <i class="fas fa-search" aria-hidden="true"></i>
              <input id="faq-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Find by question or answer">
            </label>
            <label class="faq-filter-status" for="faq-status-filter">
              <span class="sr-only">Filter by status</span>
              <select id="faq-status-filter" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
              </select>
            </label>
            <button class="faq-filter-submit" type="submit">Find</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
              <a class="faq-filter-reset" href="{{ route('user.faqs.index') }}">Clear</a>
            @endif
          </form>
          <div class="faq-list-scroll">
            @forelse ($faqs as $faq)
              <article class="faq-card">
                <div class="faq-card-top">
                  <div class="faq-question">
                    <span class="faq-icon"><i class="far fa-question-circle"></i></span>
                    <div><h3>{{ $faq->question }}</h3><span>Added {{ $faq->created_at->format('M j, Y') }}</span></div>
                  </div>
                  <div class="faq-actions">
                    <span class="faq-status {{ $faq->is_active ? 'active' : 'inactive' }}">{{ $faq->is_active ? 'Active' : 'Inactive' }}</span>
                    <button class="faq-edit-button" type="button" data-open-faq-edit="faq-edit-dialog-{{ $faq->id }}"><i class="fas fa-pen"></i> Edit</button>
                    <dialog class="faq-edit-dialog" id="faq-edit-dialog-{{ $faq->id }}" aria-labelledby="faq-edit-title-{{ $faq->id }}">
                      <div class="faq-edit-heading">
                        <div><h2 id="faq-edit-title-{{ $faq->id }}">Edit FAQ</h2><p>Update the question and answer.</p></div>
                        <button class="faq-edit-close" type="button" data-close-faq-edit aria-label="Close edit FAQ"><i class="fas fa-times"></i></button>
                      </div>
                      <form class="faq-edit-form" method="POST" action="{{ route('user.faqs.update', $faq->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="faq-field">
                          <label for="edit-question-{{ $faq->id }}">Question</label>
                          <input id="edit-question-{{ $faq->id }}" type="text" name="question" value="{{ $faq->question }}" maxlength="250" required>
                        </div>
                        <div class="faq-field">
                          <label for="edit-answer-{{ $faq->id }}">Answer</label>
                          <textarea id="edit-answer-{{ $faq->id }}" name="answer" rows="7" maxlength="5000" required>{{ $faq->answer }}</textarea>
                        </div>
                        <button class="faq-submit" type="submit">Save changes</button>
                      </form>
                    </dialog>
                    <form method="POST" action="{{ route('user.faqs.status', $faq->id) }}">
                      @csrf
                      @method('PATCH')
                      <button class="faq-status-button {{ $faq->is_active ? 'deactivate' : 'activate' }}" type="submit">
                        {{ $faq->is_active ? 'Deactivate' : 'Activate' }}
                      </button>
                    </form>
                  </div>
                </div>
                <p class="faq-answer">{{ $faq->answer }}</p>
              </article>
            @empty
              <div class="faq-empty">
                <span><i class="far fa-question-circle"></i></span>
                <h3>No FAQs yet</h3>
                <p>Add common patient questions and their answers here.</p>
              </div>
            @endforelse
          </div>
          @if ($faqs->hasPages())
            <div class="faq-pagination">{{ $faqs->links('vendor.pagination.compact') }}</div>
          @endif
        </section>
      </div>
    </div>
  </div>
</main>

<style>
  .faq-alert { margin-bottom: 18px; padding: 12px 15px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 13px; }
  .faq-layout { display: grid; grid-template-columns: minmax(290px, .78fr) minmax(0, 1.22fr); align-items: start; gap: 20px; }
  .faq-form-panel, .faq-list-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .faq-form { display: grid; gap: 16px; padding: 0 20px 21px; }
  .faq-field { min-width: 0; display: grid; gap: 7px; }
  .faq-field label { color: #59665b; font-size: 12px; font-weight: 700; }
  .faq-field input, .faq-field textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 12px; }
  .faq-field input:focus, .faq-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .faq-field textarea { resize: vertical; line-height: 1.6; }
  .faq-error { color: #a34d40; font-size: 11px; }
  .faq-submit { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; }
  .faq-submit:hover { background: var(--green); }
  .faq-list-panel { display: flex; flex-direction: column; }
  .faq-filter { display: flex; align-items: center; gap: 8px; padding: 0 20px 14px; }
  .faq-search-field { min-width: 0; min-height: 37px; display: flex; flex: 1; align-items: center; gap: 8px; padding: 0 11px; color: #849084; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; }
  .faq-search-field input { width: 100%; min-width: 0; padding: 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 11px; }
  .faq-filter-status select { min-height: 37px; max-width: 130px; padding: 0 8px; color: #59665b; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 10px; }
  .faq-filter-submit, .faq-filter-reset { min-height: 37px; display: inline-flex; align-items: center; justify-content: center; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .faq-filter-reset { color: #68756a; background: #f1f3ed; }
  .faq-list-scroll { max-height: min(68vh, 720px); overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; }
  .faq-card { padding: 17px 20px; border-top: 1px solid var(--line); }
  .faq-card-top, .faq-question { display: flex; align-items: center; }
  .faq-card-top { justify-content: space-between; gap: 12px; }
  .faq-question { min-width: 0; gap: 10px; }
  .faq-icon { width: 36px; height: 36px; flex: 0 0 36px; display: grid; place-items: center; color: var(--green-dark); background: var(--sage); border-radius: 50%; }
  .faq-question div { min-width: 0; }
  .faq-question h3 { margin: 0 0 4px; font-size: 12px; line-height: 1.5; }
  .faq-question div > span { color: var(--muted); font-size: 10px; }
  .faq-actions { display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: 7px; }
  .faq-actions form { margin: 0; }
  .faq-status, .faq-status-button { min-height: 27px; display: inline-flex; align-items: center; justify-content: center; padding: 0 9px; border: 1px solid transparent; border-radius: 6px; font-size: 10px; font-weight: 700; white-space: nowrap; }
  .faq-status.active { color: #365844; background: #e8f1e6; }
  .faq-status.inactive { color: #8b6557; background: #f5ece7; }
  .faq-status-button { cursor: pointer; }
  .faq-status-button.deactivate { color: #8b6557; background: #fff; border-color: #ead9d0; }
  .faq-status-button.activate { color: #365844; background: #fff; border-color: #d5e6d2; }
  .faq-edit-button { min-height: 27px; display: inline-flex; align-items: center; gap: 5px; padding: 0 9px; color: #526255; background: #f1f3ed; border: 0; border-radius: 6px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .faq-answer { margin: 12px 0 0 46px; color: #647064; font-size: 12px; line-height: 1.65; white-space: pre-line; overflow-wrap: anywhere; }
  .faq-edit-dialog { width: min(480px, calc(100vw - 32px)); max-height: min(86vh, 760px); padding: 0; overflow-y: auto; color: var(--ink); background: #fffefa; border: 1px solid var(--line); border-radius: 13px; box-shadow: 0 24px 70px rgba(34,48,38,.24); }
  .faq-edit-dialog::backdrop { background: rgba(28,40,32,.42); backdrop-filter: blur(2px); }
  .faq-edit-heading { position: sticky; z-index: 1; top: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 17px 20px; background: #fffefa; border-bottom: 1px solid var(--line); }
  .faq-edit-heading h2 { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
  .faq-edit-heading p { margin: 0; color: var(--muted); font-size: 11px; }
  .faq-edit-close { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #647064; background: #f1f3ed; border: 0; border-radius: 50%; cursor: pointer; }
  .faq-edit-form { display: grid; gap: 15px; padding: 18px 20px 20px; }
  .faq-empty { display: grid; justify-items: center; padding: 46px 20px; text-align: center; }
  .faq-empty > span { width: 46px; height: 46px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .faq-empty h3 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .faq-empty p { margin: 0; color: var(--muted); font-size: 12px; }
  .faq-pagination { display: flex; justify-content: center; padding: 12px 20px 18px; border-top: 1px solid var(--line); }
  .faq-pagination .pagination-links { display: flex; align-items: center; gap: 5px; }
  .faq-pagination .pagination-links a, .faq-pagination .pagination-links > span { min-width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: #68756a; font-size: 11px; }
  .faq-pagination .pagination-links [aria-current=page] { color: #fff; background: var(--green); border-color: var(--green); }
  .faq-pagination svg { width: 15px; height: 15px; }
  @media (max-width: 760px) { .faq-layout { grid-template-columns: 1fr; } .faq-card-top { align-items: flex-start; flex-direction: column; } .faq-actions { justify-content: flex-start; } .faq-answer { margin-left: 0; } .faq-filter { flex-wrap: wrap; } .faq-search-field { flex-basis: 100%; } }
</style>
<script>
  document.querySelectorAll('[data-open-faq-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.openFaqEdit);
      if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-faq-edit]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
</script>
@endsection
