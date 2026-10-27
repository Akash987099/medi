<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f7f6f1">
  <title>Share your experience | MediCare</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * { box-sizing: border-box; }
    body { min-height: 100vh; display: grid; place-items: center; margin: 0; padding: 24px 14px; color: #26382f; background: #f7f6f1; font-family: 'DM Sans', sans-serif; }
    .review-page { width: min(100%, 480px); overflow: hidden; background: #fffefa; border: 1px solid #e9e8df; border-radius: 17px; box-shadow: 0 18px 55px rgba(46,60,48,.09); }
    .review-page-header { padding: 26px 28px 20px; text-align: center; background: #e8eee5; border-bottom: 1px solid #dfe7dc; }
    .review-logo { width: 40px; height: 40px; display: grid; place-items: center; margin: 0 auto 11px; color: #fff; background: #54745d; border-radius: 13px 13px 13px 5px; }
    .review-page-header p { margin: 0 0 7px; color: #54745d; font-size: 9px; font-weight: 700; letter-spacing: 1.3px; text-transform: uppercase; }
    .review-page-header h1 { margin: 0 0 7px; font-family: 'Playfair Display', serif; font-size: 25px; font-weight: 600; }
    .review-page-header span { color: #6e796f; font-size: 11px; }
    .review-form { display: grid; gap: 17px; padding: 24px 28px 27px; }
    .field { display: grid; gap: 7px; }
    .field > label, .rating-field legend { color: #59665b; font-size: 12px; font-weight: 700; }
    .field input, .field textarea { width: 100%; padding: 12px; color: #26382f; background: #fff; border: 1px solid #e2e5dc; border-radius: 8px; outline: none; font: inherit; font-size: 13px; }
    .field input:focus, .field textarea:focus { border-color: #93aa93; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
    .field textarea { resize: vertical; line-height: 1.55; }
    .rating-field { margin: 0; padding: 0; border: 0; }
    .rating-field legend { margin-bottom: 8px; }
    .stars { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 6px; }
    .stars input { position: absolute; width: 1px; height: 1px; opacity: 0; }
    .stars label { color: #d4d9cf; cursor: pointer; font-size: 34px; line-height: 1; }
    .stars label:hover, .stars label:hover ~ label, .stars label:has(input:checked), .stars label:has(input:checked) ~ label { color: #c39a58; }
    .stars input:focus-visible + span { outline: 2px solid #54745d; outline-offset: 3px; }
    .error { color: #a34d40; font-size: 11px; }
    .success { margin: 20px 24px 0; padding: 12px 14px; color: #365844; background: #e8f1e6; border: 1px solid #d5e6d2; border-radius: 9px; font-size: 12px; text-align: center; }
    .submit { min-height: 45px; display: flex; align-items: center; justify-content: center; gap: 9px; color: #fff; background: #304b3b; border: 0; border-radius: 8px; cursor: pointer; font: inherit; font-size: 12px; font-weight: 700; }
    .submit:hover { background: #54745d; }
    .privacy { margin: -6px 0 0; color: #879087; font-size: 10px; line-height: 1.5; text-align: center; }
    @media (max-width: 480px) { .review-page-header { padding-right: 20px; padding-left: 20px; } .review-form { padding-right: 20px; padding-left: 20px; } }
  </style>
</head>
<body>
  <main class="review-page">
    <header class="review-page-header">
      <span class="review-logo"><i class="fas fa-plus"></i></span>
      <p>Patient feedback</p>
      <h1>Share your experience</h1>
      <span>Your feedback for <strong>{{ $user->name }}</strong> is appreciated.</span>
    </header>
    @if (session('review_submitted'))
      <div class="success" role="status"><i class="fas fa-check-circle"></i> Thank you. Your review has been submitted.</div>
    @endif
    <form class="review-form" method="POST" action="{{ route('public.reviews.store', $token) }}">
      @csrf
      <div class="field">
        <label for="reviewer_name">Your name</label>
        <input id="reviewer_name" type="text" name="reviewer_name" value="{{ old('reviewer_name') }}" maxlength="120" autocomplete="name" required>
        @error('reviewer_name')<span class="error">{{ $message }}</span>@enderror
      </div>
      <fieldset class="rating-field">
        <legend>Your rating</legend>
        <div class="stars" role="radiogroup" aria-label="Rate from one to five stars">
          @for ($rating = 5; $rating >= 1; $rating--)
            <label>
              <input type="radio" name="rating" value="{{ $rating }}" @checked((int) old('rating', 5) === $rating) required>
              <span aria-label="{{ $rating }} {{ \Illuminate\Support\Str::plural('star', $rating) }}">★</span>
            </label>
          @endfor
        </div>
        @error('rating')<span class="error">{{ $message }}</span>@enderror
      </fieldset>
      <div class="field">
        <label for="review_text">Your review</label>
        <textarea id="review_text" name="review_text" rows="4" maxlength="3000" placeholder="Tell us about your experience..." required>{{ old('review_text') }}</textarea>
        @error('review_text')<span class="error">{{ $message }}</span>@enderror
      </div>
      <button class="submit" type="submit">Submit review <i class="fas fa-arrow-right"></i></button>
      <p class="privacy"><i class="fas fa-lock"></i> Your feedback is shared with {{ $user->name }} to help improve care.</p>
    </form>
  </main>
</body>
</html>
