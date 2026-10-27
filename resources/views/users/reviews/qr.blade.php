@extends('users.layout.app')

@section('title', 'Review QR codes')

@section('content')
<main class="main-panel">
  <header class="topbar">
    <div class="topbar-left">
      <button class="icon-button mobile-menu" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="main-navigation">
        <i class="fas fa-bars"></i>
      </button>
      <div class="breadcrumb"><a href="{{ route('user.reviews.index') }}">Reviews</a><span> / </span>QR codes</div>
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
      <section class="page-heading" aria-labelledby="review-qr-title">
        <div>
          <p class="eyebrow">Share your feedback page</p>
          <h1 id="review-qr-title">Review QR codes</h1>
          <p class="page-subtitle">Let visitors scan a code to leave a review for {{ $user->name }}.</p>
        </div>
        <a class="back-to-reviews" href="{{ route('user.reviews.index') }}"><i class="fas fa-arrow-left"></i> Back to reviews</a>
      </section>

      <div class="qr-help"><i class="fas fa-info-circle"></i><span>Both designs open your secure review form. Print a card and place it where visitors can scan it.</span></div>

      <div class="qr-design-grid">
        <article class="qr-design-card qr-design-light" id="qr-design-light" data-print-title="MediCare review card - Classic">
          <div class="qr-design-preview">
            <div class="qr-brand"><span class="qr-brand-mark"><i class="fas fa-plus"></i></span><span>MediCare</span></div>
            <div class="qr-preview-copy">
              <p class="qr-overline">YOUR FEEDBACK MATTERS</p>
              <h2>How was your experience?</h2>
              <p>Share a few words about your visit with <strong>{{ $user->name }}</strong>.</p>
            </div>
            <div class="qr-image">{!! $qrCode !!}</div>
            <div class="qr-scan-hint"><i class="fas fa-camera"></i> Scan to write a review</div>
            <div class="qr-stars" aria-hidden="true">★★★★★</div>
          </div>
          <div class="qr-design-footer">
            <div><h2>Design 1 <span>Classic</span></h2><p>Bright, simple and easy to display.</p></div>
            <button type="button" class="qr-print-button" data-print-design="qr-design-light"><i class="fas fa-print"></i> Print</button>
          </div>
        </article>

        <article class="qr-design-card qr-design-green" id="qr-design-green" data-print-title="MediCare review card - Signature">
          <div class="qr-design-preview">
            <div class="qr-green-top">
              <div class="qr-brand"><span class="qr-brand-mark"><i class="fas fa-plus"></i></span><span>MediCare</span></div>
              <span class="qr-green-label">CARE THAT CONNECTS</span>
            </div>
            <div class="qr-green-message">
              <p class="qr-overline">A MOMENT FOR FEEDBACK</p>
              <h2>Your experience<br>can help others.</h2>
              <p>Leave a review for <strong>{{ $user->name }}</strong>.</p>
            </div>
            <div class="qr-green-code">
              <div class="qr-image">{!! $qrCode !!}</div>
              <span>SCAN TO REVIEW</span>
            </div>
            <div class="qr-green-bottom"><span>Thank you for trusting us with your care.</span><span class="qr-stars" aria-hidden="true">★★★★★</span></div>
          </div>
          <div class="qr-design-footer">
            <div><h2>Design 2 <span>Signature</span></h2><p>A warm, premium style with a bold green finish.</p></div>
            <button type="button" class="qr-print-button" data-print-design="qr-design-green"><i class="fas fa-print"></i> Print</button>
          </div>
        </article>
      </div>

      <section class="qr-link-panel">
        <div><strong>Review form link</strong><p>Share this URL digitally if a printed QR card is not convenient.</p></div>
        <div class="qr-link-copy"><input type="text" value="{{ $reviewUrl }}" readonly aria-label="Public review page link"><button type="button" data-copy-review-link><i class="far fa-copy"></i> Copy link</button><span class="qr-copy-status" role="status" aria-live="polite"></span></div>
      </section>
    </div>
  </div>
</main>

<style>
  .back-to-reviews, .qr-print-button { min-height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 13px; color: #526255; background: var(--white); border: 1px solid var(--line); border-radius: 8px; cursor: pointer; font-size: 11px; font-weight: 700; white-space: nowrap; }
  .back-to-reviews:hover, .qr-print-button:hover { color: var(--green-dark); background: var(--sage); }
  .qr-help { display: flex; align-items: flex-start; gap: 10px; margin-bottom: 18px; padding: 13px 15px; color: #526255; background: #edf2ea; border: 1px solid #dfe8dc; border-radius: 9px; font-size: 11px; line-height: 1.55; }
  .qr-help i { margin-top: 2px; color: var(--green); }
  .qr-design-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); align-items: start; gap: 20px; }
  .qr-design-card { min-width: 0; overflow: hidden; background: var(--white); border: 1px solid var(--line); border-radius: 13px; box-shadow: var(--shadow); }
  .qr-design-preview { min-height: 465px; display: flex; flex-direction: column; align-items: center; padding: 27px 24px 22px; text-align: center; }
  .qr-design-light .qr-design-preview { background: #fffefa; }
  .qr-brand { display: flex; align-items: center; gap: 8px; color: #304b3b; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 700; }
  .qr-brand-mark { width: 25px; height: 25px; display: grid; place-items: center; color: #fff; background: #54745d; border-radius: 8px 8px 8px 3px; font-family: 'DM Sans', sans-serif; font-size: 11px; }
  .qr-preview-copy { margin-top: 25px; }
  .qr-overline { margin: 0 0 8px; color: #718471; font-size: 9px; font-weight: 700; letter-spacing: 1.3px; }
  .qr-preview-copy h2, .qr-green-message h2 { margin: 0 0 8px; color: #26382f; font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 600; line-height: 1.2; }
  .qr-preview-copy > p:last-child, .qr-green-message > p:last-child { max-width: 300px; margin: 0 auto; color: #788277; font-size: 11px; line-height: 1.5; }
  .qr-preview-copy strong, .qr-green-message strong { color: #405b46; }
  .qr-image { width: 190px; height: 190px; display: grid; place-items: center; flex: 0 0 auto; padding: 8px; background: #fff; border: 1px solid #eef0e9; border-radius: 12px; }
  .qr-image svg { display: block; width: 100%; height: 100%; }
  .qr-scan-hint { display: flex; align-items: center; gap: 7px; margin-top: 13px; color: #56685a; font-size: 10px; font-weight: 700; }
  .qr-stars { margin-top: 12px; color: #c39a58; font-size: 14px; letter-spacing: 3px; }
  .qr-design-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 15px 17px; border-top: 1px solid var(--line); }
  .qr-design-footer h2 { display: flex; align-items: center; gap: 7px; margin: 0 0 4px; font-size: 12px; }
  .qr-design-footer h2 span { padding: 3px 6px; color: #667566; background: #edf1e9; border-radius: 10px; font-size: 8px; text-transform: uppercase; letter-spacing: .5px; }
  .qr-design-footer p { margin: 0; color: var(--muted); font-size: 10px; }
  .qr-print-button { min-height: 33px; flex: 0 0 auto; }
  .qr-design-green .qr-design-preview { position: relative; overflow: hidden; justify-content: space-between; color: #fff; background: #304b3b; }
  .qr-design-green .qr-design-preview:before { position: absolute; top: 92px; right: -80px; width: 220px; height: 220px; border: 1px solid rgba(255,255,255,.11); border-radius: 50%; content: ''; }
  .qr-green-top, .qr-green-bottom { width: 100%; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
  .qr-green-top .qr-brand { color: #fff; }
  .qr-green-top .qr-brand-mark { color: #304b3b; background: #e7ede3; }
  .qr-green-label { color: #d5e2d4; font-size: 8px; font-weight: 700; letter-spacing: .9px; }
  .qr-green-message { position: relative; z-index: 1; width: 100%; margin: 22px 0 16px; }
  .qr-green-message .qr-overline { color: #bfd0be; }
  .qr-green-message h2 { color: #fff; font-size: 23px; }
  .qr-green-message > p:last-child { color: #d3ded1; }
  .qr-green-message strong { color: #fff; }
  .qr-green-code { position: relative; z-index: 1; display: grid; justify-items: center; gap: 7px; padding: 11px 13px 9px; background: #fff; border-radius: 13px; }
  .qr-green-code .qr-image { width: 166px; height: 166px; padding: 0; border: 0; border-radius: 0; }
  .qr-green-code > span { color: #304b3b; font-size: 8px; font-weight: 800; letter-spacing: 1.2px; }
  .qr-green-bottom { margin-top: 16px; color: #e0e8dd; font-size: 9px; }
  .qr-green-bottom .qr-stars { margin: 0; color: #e5bd70; font-size: 12px; }
  .qr-link-panel { display: grid; grid-template-columns: minmax(0, .7fr) minmax(260px, 1.3fr); align-items: center; gap: 20px; margin-top: 20px; padding: 17px 19px; background: var(--white); border: 1px solid var(--line); border-radius: 11px; }
  .qr-link-panel strong { font-size: 12px; }
  .qr-link-panel p { margin: 4px 0 0; color: var(--muted); font-size: 10px; }
  .qr-link-copy { display: flex; gap: 7px; }
  .qr-link-copy input { min-width: 0; flex: 1; padding: 0 10px; color: #667566; background: #fff; border: 1px solid var(--line); border-radius: 7px; font-size: 10px; }
  .qr-link-copy button { min-height: 36px; display: inline-flex; align-items: center; gap: 7px; padding: 0 11px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 700; white-space: nowrap; }
  .qr-copy-status { align-self: center; color: #a34d40; font-size: 10px; }
  @media (max-width: 850px) { .qr-design-grid { grid-template-columns: 1fr; } .qr-design-preview { min-height: 440px; } }
  @media (max-width: 600px) { .qr-link-panel { grid-template-columns: 1fr; } .qr-link-copy { flex-wrap: wrap; } .qr-link-copy input { min-height: 36px; flex-basis: 100%; } }
  @media print {
    @page { size: A5 portrait; margin: 0; }
    html, body { width: 148mm; height: 210mm; min-height: 0; margin: 0 !important; padding: 0 !important; overflow: hidden !important; background: #fff !important; print-color-adjust: exact !important; -webkit-print-color-adjust: exact !important; }
    body * { visibility: hidden !important; }
    .app-shell, .main-panel, .content-scroll, .content-wrap, .qr-design-grid, .qr-design-card.is-printing, .qr-design-card.is-printing * { visibility: visible !important; }
    .sidebar, .sidebar-backdrop, .topbar, .page-heading, .qr-help, .qr-link-panel, .qr-design-footer, .review-alert { display: none !important; }
    .app-shell, .main-panel, .content-scroll, .content-wrap, .qr-design-grid { position: static !important; width: auto !important; height: auto !important; min-height: 0 !important; margin: 0 !important; padding: 0 !important; overflow: visible !important; background: transparent !important; border: 0 !important; box-shadow: none !important; }
    .qr-design-grid { display: block !important; }
    .qr-design-card, .qr-design-card.is-printing { display: none !important; }
    .qr-design-card.is-printing { position: fixed !important; inset: 0 !important; z-index: 99999 !important; display: block !important; width: 148mm !important; height: 210mm !important; min-height: 0 !important; overflow: hidden !important; border: 0 !important; border-radius: 0 !important; box-shadow: none !important; }
    .qr-design-card.is-printing .qr-design-preview { width: 148mm !important; height: 210mm !important; min-height: 0 !important; padding: 15mm 12mm !important; justify-content: space-between !important; gap: 0 !important; border: 0 !important; border-radius: 0 !important; print-color-adjust: exact !important; -webkit-print-color-adjust: exact !important; }
    .qr-design-card.is-printing .qr-preview-copy { margin-top: 0 !important; }
    .qr-design-card.is-printing .qr-preview-copy h2 { font-size: 30pt !important; }
    .qr-design-card.is-printing .qr-preview-copy > p:last-child, .qr-design-card.is-printing .qr-green-message > p:last-child { max-width: 100mm !important; font-size: 12pt !important; }
    .qr-design-card.is-printing .qr-image { width: 75mm !important; height: 75mm !important; padding: 3mm !important; border-radius: 3mm !important; }
    .qr-design-card.is-printing .qr-green-code { padding: 4mm !important; border-radius: 3mm !important; }
    .qr-design-card.is-printing .qr-green-code .qr-image { width: 68mm !important; height: 68mm !important; padding: 0 !important; border-radius: 0 !important; }
    .qr-design-card.is-printing .qr-green-message { margin: 0 !important; }
    .qr-design-card.is-printing .qr-green-message h2 { font-size: 29pt !important; }
    .qr-design-card.is-printing .qr-green-top .qr-brand { font-size: 20pt !important; }
    .qr-design-card.is-printing .qr-green-label, .qr-design-card.is-printing .qr-green-code > span { font-size: 9pt !important; }
    .qr-design-card.is-printing .qr-green-bottom { font-size: 10pt !important; }
    .qr-design-card.is-printing .qr-stars { font-size: 19pt !important; }
  }
</style>
<script>
  document.querySelectorAll('[data-print-design]').forEach((button) => {
    button.addEventListener('click', () => {
      const card = document.getElementById(button.dataset.printDesign);
      if (!card) return;
      card.classList.add('is-printing');
      const cleanup = () => {
        card.classList.remove('is-printing');
        document.title = originalTitle;
      };
      const originalTitle = document.title;
      document.title = card.dataset.printTitle || originalTitle;
      window.addEventListener('afterprint', cleanup, { once: true });
      window.setTimeout(() => window.print(), 100);
      window.setTimeout(cleanup, 60000);
    });
  });

  const copyButton = document.querySelector('[data-copy-review-link]');
  if (copyButton) {
    copyButton.addEventListener('click', async () => {
      const link = document.querySelector('.qr-link-copy input');
      const status = document.querySelector('.qr-copy-status');
      if (!(link instanceof HTMLInputElement)) return;
      try {
        await navigator.clipboard.writeText(link.value);
        copyButton.innerHTML = '<i class="fas fa-check"></i> Copied';
      } catch (error) {
        link.select();
        if (document.execCommand('copy')) {
          copyButton.innerHTML = '<i class="fas fa-check"></i> Copied';
        } else if (status) {
          status.textContent = 'Copy failed. Select and copy the link.';
        }
      }
    });
  }
</script>
@endsection
