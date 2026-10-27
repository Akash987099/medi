@extends('users.layout.app')

@section('title', 'Reviews')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><strong>Workspace</strong><span> / </span>Reviews</div>
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
      <section class="page-heading" aria-labelledby="reviews-title">
        <div>
          <p class="eyebrow">Patient feedback</p>
          <h1 id="reviews-title">Reviews</h1>
          <p class="page-subtitle">Keep feedback you have received together in one place.</p>
        </div>
        <a class="review-qr-link" href="{{ route('user.reviews.qr') }}"><i class="fas fa-qrcode"></i> Review QR codes</a>
      </section>

      @if (session('status'))
        <div class="review-alert review-alert-success" role="status">{{ session('status') }}</div>
      @endif

      <div class="review-layout">
        <section class="panel review-form-panel" aria-labelledby="add-review-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="add-review-title">Add a review</h2>
              <p class="panel-caption">Enter the feedback and rating you have received.</p>
            </div>
          </div>
          <form class="review-form" method="POST" action="{{ route('user.reviews.store') }}">
            @csrf
            <div class="review-field">
              <label for="reviewer_name">Reviewer name</label>
              <input id="reviewer_name" type="text" name="reviewer_name" value="{{ old('reviewer_name') }}" maxlength="120" required>
              @error('reviewer_name')<span class="review-error">{{ $message }}</span>@enderror
            </div>
            <fieldset class="review-field rating-field">
              <legend>Rating</legend>
              <div class="rating-options" role="radiogroup" aria-label="Rating from one to five stars">
                @for ($rating = 5; $rating >= 1; $rating--)
                  <label class="rating-option">
                    <input type="radio" name="rating" value="{{ $rating }}" @checked((int) old('rating', 5) === $rating) required>
                    <span aria-label="{{ $rating }} {{ \Illuminate\Support\Str::plural('star', $rating) }}">★</span>
                  </label>
                @endfor
              </div>
              @error('rating')<span class="review-error">{{ $message }}</span>@enderror
            </fieldset>
            <div class="review-field">
              <label for="review_text">Review</label>
              <textarea id="review_text" name="review_text" rows="5" maxlength="3000" required>{{ old('review_text') }}</textarea>
              @error('review_text')<span class="review-error">{{ $message }}</span>@enderror
            </div>
            <button class="review-submit" type="submit"><i class="fas fa-plus"></i> Save review</button>
            <p class="review-disclaimer">Reviews are saved to your workspace.</p>
          </form>
        </section>

        <section class="panel review-list-panel" aria-labelledby="saved-reviews-title">
          <div class="panel-heading">
            <div>
              <h2 class="panel-title" id="saved-reviews-title">Saved reviews</h2>
              <p class="panel-caption">{{ $reviews->total() }} {{ \Illuminate\Support\Str::plural('review', $reviews->total()) }} in your workspace</p>
            </div>
          </div>
          <form class="review-filter" method="GET" action="{{ route('user.reviews.index') }}">
            <label class="review-search-field" for="review-search">
              <i class="fas fa-search" aria-hidden="true"></i>
              <input id="review-search" type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="100" placeholder="Find by reviewer or review text">
            </label>
            <label class="review-filter-status" for="review-status-filter">
              <span class="sr-only">Filter by status</span>
              <select id="review-status-filter" name="status">
                <option value="all" @selected(($filters['status'] ?? 'all') === 'all')>All statuses</option>
                <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
              </select>
            </label>
            <button class="review-filter-submit" type="submit">Find</button>
            @if (($filters['q'] ?? '') !== '' || ($filters['status'] ?? 'all') !== 'all')
              <a class="review-filter-reset" href="{{ route('user.reviews.index') }}">Clear</a>
            @endif
          </form>
          <div class="review-list-scroll">
          @forelse ($reviews as $review)
            <article class="review-card">
              <div class="review-card-top">
                <div class="review-reviewer">
                  <span class="review-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($review->reviewer_name, 0, 1)) }}</span>
                  <div><strong>{{ $review->reviewer_name }}</strong><span>{{ $review->created_at->format('M j, Y') }}</span></div>
                </div>
                <div class="review-actions">
                  <span class="review-status {{ $review->is_active ? 'active' : 'inactive' }}">{{ $review->is_active ? 'Active' : 'Inactive' }}</span>
                  <button class="review-edit-button" type="button" data-open-review-edit="review-edit-dialog-{{ $review->id }}"><i class="fas fa-pen"></i> Edit</button>
                  <dialog class="review-edit-dialog" id="review-edit-dialog-{{ $review->id }}" aria-labelledby="review-edit-title-{{ $review->id }}">
                    <div class="review-edit-heading">
                      <div>
                        <h2 id="review-edit-title-{{ $review->id }}">Edit review</h2>
                        <p>Update the saved review details.</p>
                      </div>
                      <button class="review-edit-close" type="button" data-close-review-edit aria-label="Close edit review"><i class="fas fa-times"></i></button>
                    </div>
                    <form class="review-edit-form" method="POST" action="{{ route('user.reviews.update', $review->id) }}">
                      @csrf
                      @method('PUT')
                      <div class="review-field">
                        <label for="reviewer_name_{{ $review->id }}">Reviewer name</label>
                        <input id="reviewer_name_{{ $review->id }}" type="text" name="reviewer_name" value="{{ $review->reviewer_name }}" maxlength="120" required>
                      </div>
                      <fieldset class="review-field rating-field">
                        <legend>Rating</legend>
                        <div class="rating-options" role="radiogroup" aria-label="Rating from one to five stars">
                          @for ($rating = 5; $rating >= 1; $rating--)
                            <label class="rating-option">
                              <input type="radio" name="rating" value="{{ $rating }}" @checked($review->rating === $rating) required>
                              <span aria-label="{{ $rating }} {{ \Illuminate\Support\Str::plural('star', $rating) }}">★</span>
                            </label>
                          @endfor
                        </div>
                      </fieldset>
                      <div class="review-field">
                        <label for="review_text_{{ $review->id }}">Review</label>
                        <textarea id="review_text_{{ $review->id }}" name="review_text" rows="4" maxlength="3000" required>{{ $review->review_text }}</textarea>
                      </div>
                      <button class="review-submit" type="submit">Save changes</button>
                    </form>
                  </dialog>
                  <form method="POST" action="{{ route('user.reviews.status', $review->id) }}">
                    @csrf
                    @method('PATCH')
                    <button class="review-status-button {{ $review->is_active ? 'deactivate' : 'activate' }}" type="submit">
                      {{ $review->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                  </form>
                </div>
              </div>
              <div class="review-stars" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}<span>{{ str_repeat('★', 5 - $review->rating) }}</span></div>
              <p class="review-copy">{{ $review->review_text }}</p>
            </article>
          @empty
            <div class="review-empty">
              <span><i class="far fa-star"></i></span>
              <h3>No reviews yet</h3>
              <p>Reviews you add will appear here.</p>
            </div>
          @endforelse
          </div>
          @if ($reviews->hasPages())
            <div class="review-pagination">{{ $reviews->links('vendor.pagination.compact') }}</div>
          @endif
        </section>
      </div>
    </div>
  </div>
</main>

<style>
  .review-alert { margin-bottom: 18px; padding: 12px 15px; border-radius: 9px; font-size: 13px; }
  .review-alert-success { color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; }
  .review-qr-link { min-height: 39px; display: inline-flex; align-items: center; gap: 8px; padding: 0 13px; color: #fff; background: var(--green-dark); border-radius: 8px; font-size: 11px; font-weight: 700; white-space: nowrap; }
  .review-qr-link:hover { background: var(--green); }
  .review-layout { display: grid; grid-template-columns: minmax(290px, .78fr) minmax(0, 1.22fr); align-items: start; gap: 20px; }
  .review-form-panel, .review-list-panel { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .review-list-panel { display: flex; flex-direction: column; }
  .review-list-scroll { max-height: min(68vh, 720px); overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; }
  .review-list-scroll::-webkit-scrollbar, .review-edit-dialog::-webkit-scrollbar { width: 8px; }
  .review-list-scroll::-webkit-scrollbar-thumb, .review-edit-dialog::-webkit-scrollbar-thumb { background: #c7d1c5; border: 2px solid transparent; border-radius: 8px; background-clip: padding-box; }
  .review-filter { display: flex; align-items: center; gap: 8px; padding: 0 20px 14px; }
  .review-search-field { min-width: 0; min-height: 37px; display: flex; flex: 1; align-items: center; gap: 8px; padding: 0 11px; color: #849084; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; }
  .review-search-field input { width: 100%; min-width: 0; padding: 0; color: var(--ink); background: transparent; border: 0; outline: 0; font: inherit; font-size: 11px; }
  .review-search-field:focus-within, .review-filter-status select:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .review-filter-status select { min-height: 37px; max-width: 130px; padding: 0 8px; color: #59665b; background: #fff; border: 1px solid #e2e5dc; border-radius: 7px; font: inherit; font-size: 10px; }
  .review-filter-submit, .review-filter-reset { min-height: 37px; display: inline-flex; align-items: center; justify-content: center; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .review-filter-reset { color: #68756a; background: #f1f3ed; }
  .review-form { display: grid; gap: 16px; padding: 0 20px 21px; }
  .review-field { min-width: 0; display: grid; gap: 7px; }
  .review-field label, .rating-field legend { color: #59665b; font-size: 12px; font-weight: 700; }
  .review-field input:not([type=radio]), .review-field select, .review-field textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font-size: 13px; }
  .review-field input:focus, .review-field select:focus, .review-field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
  .review-field textarea { resize: vertical; line-height: 1.55; }
  .rating-field { margin: 0; padding: 0; border: 0; }
  .rating-field legend { margin-bottom: 7px; }
  .rating-options { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 4px; }
  .rating-option input { position: absolute; opacity: 0; }
  .rating-option span { color: #d4d9cf; cursor: pointer; font-size: 26px; line-height: 1.1; letter-spacing: 1px; }
  .rating-option:hover span, .rating-option:hover ~ .rating-option span { color: #c39a58; }
  .rating-option:has(input:checked) span, .rating-option:has(input:checked) ~ .rating-option span { color: #c39a58; }
  .rating-option input:focus-visible + span { outline: 2px solid #54745d; outline-offset: 3px; border-radius: 3px; }
  .review-error { color: #a34d40; font-size: 11px; }
  .review-submit { min-height: 43px; display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 0 17px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700; }
  .review-submit:hover { background: var(--green); }
  .review-disclaimer { margin: -5px 0 0; color: var(--muted); font-size: 10px; line-height: 1.5; }
  .review-card { padding: 17px 20px; border-top: 1px solid var(--line); }
  .review-card-top, .review-reviewer { display: flex; align-items: center; }
  .review-card-top { justify-content: space-between; gap: 12px; }
  .review-actions { display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: 7px; }
  .review-actions form { margin: 0; }
  .review-status, .review-status-button { min-height: 27px; display: inline-flex; align-items: center; justify-content: center; padding: 0 9px; border: 1px solid transparent; border-radius: 6px; font-size: 10px; font-weight: 700; white-space: nowrap; }
  .review-status.active { color: #365844; background: #e8f1e6; }
  .review-status.inactive { color: #8b6557; background: #f5ece7; }
  .review-status-button { cursor: pointer; }
  .review-status-button.deactivate { color: #8b6557; background: #fff; border-color: #ead9d0; }
  .review-status-button.activate { color: #365844; background: #fff; border-color: #d5e6d2; }
  .review-edit-button { min-height: 27px; display: inline-flex; align-items: center; gap: 5px; padding: 0 9px; color: #526255; background: #f1f3ed; border: 0; border-radius: 6px; cursor: pointer; font-size: 10px; font-weight: 700; }
  .review-edit-dialog { width: min(440px, calc(100vw - 32px)); max-height: min(86vh, 760px); padding: 0; overflow-y: auto; color: var(--ink); background: #fffefa; border: 1px solid var(--line); border-radius: 13px; box-shadow: 0 24px 70px rgba(34, 48, 38, .24); }
  .review-edit-dialog::backdrop { background: rgba(28, 40, 32, .42); backdrop-filter: blur(2px); }
  .review-edit-heading { position: sticky; z-index: 1; top: 0; display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 17px 20px; background: #fffefa; border-bottom: 1px solid var(--line); }
  .review-edit-heading h2 { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
  .review-edit-heading p { margin: 0; color: var(--muted); font-size: 11px; }
  .review-edit-close { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #647064; background: #f1f3ed; border: 0; border-radius: 50%; cursor: pointer; }
  .review-edit-close:hover { color: #fff; background: var(--green-dark); }
  .review-edit-form { display: grid; gap: 15px; padding: 18px 20px 20px; }
  .review-reviewer { min-width: 0; gap: 10px; }
  .review-avatar { width: 37px; height: 37px; flex: 0 0 37px; display: grid; place-items: center; color: var(--green-dark); background: var(--sage); border-radius: 50%; font-family: 'Playfair Display', serif; font-weight: 700; }
  .review-reviewer div { min-width: 0; display: grid; gap: 3px; }
  .review-reviewer strong { overflow: hidden; font-size: 12px; text-overflow: ellipsis; white-space: nowrap; }
  .review-reviewer div span { color: var(--muted); font-size: 10px; }
  .review-stars { margin: 13px 0 7px; color: #c39a58; font-size: 15px; letter-spacing: 1px; }
  .review-stars span { color: #d4d9cf; }
  .review-copy { margin: 0; color: #647064; font-size: 12px; line-height: 1.65; white-space: pre-line; overflow-wrap: anywhere; }
  .review-empty { display: grid; justify-items: center; padding: 46px 20px; text-align: center; }
  .review-empty > span { width: 46px; height: 46px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 50%; }
  .review-empty h3 { margin: 14px 0 5px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
  .review-empty p { margin: 0; color: var(--muted); font-size: 12px; }
  .review-pagination { display: flex; justify-content: center; padding: 12px 20px 18px; border-top: 1px solid var(--line); }
  .review-pagination .pagination-links { display: flex; align-items: center; gap: 5px; }
  .review-pagination .pagination-links a, .review-pagination .pagination-links > span { min-width: 30px; height: 30px; display: grid; place-items: center; border: 1px solid var(--line); border-radius: 6px; color: #68756a; font-size: 11px; }
  .review-pagination .pagination-links [aria-current=page] { color: #fff; background: var(--green); border-color: var(--green); }
  .review-pagination svg { width: 15px; height: 15px; }
  @media (max-width: 760px) { .review-layout { grid-template-columns: 1fr; } .review-card-top { align-items: flex-start; flex-direction: column; } .review-actions { justify-content: flex-start; } .review-list-scroll { max-height: 65vh; } .review-filter { flex-wrap: wrap; } .review-search-field { flex-basis: 100%; } }
</style>
<script>
  document.querySelectorAll('[data-open-review-edit]').forEach((button) => {
    button.addEventListener('click', () => {
      const dialog = document.getElementById(button.dataset.openReviewEdit);
      if (dialog instanceof HTMLDialogElement) dialog.showModal();
    });
  });
  document.querySelectorAll('[data-close-review-edit]').forEach((button) => {
    button.addEventListener('click', () => button.closest('dialog')?.close());
  });
</script>
@endsection
