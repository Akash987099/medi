<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f6f7f2">
  <title>Care Partner Registration | MediCare</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root { --ink: #26382f; --muted: #748078; --line: #e3e8df; --green: #53745d; --green-dark: #304b3b; --paper: #f6f7f2; --sage: #e7eee5; --error: #a33b32; }
    * { box-sizing: border-box; }
    [hidden] { display: none !important; }
    body { min-height: 100vh; margin: 0; color: var(--ink); background: radial-gradient(ellipse at 82% 0, rgba(214,228,211,.55), transparent 35%), var(--paper); font-family: 'DM Sans', sans-serif; }
    a { color: inherit; text-decoration: none; }
    .topbar { background: rgba(255,254,250,.94); border-bottom: 1px solid var(--line); }
    .topbar-inner { width: min(1120px, calc(100% - 36px)); min-height: 70px; display: flex; align-items: center; justify-content: space-between; margin: auto; }
    .brand { display: inline-flex; align-items: center; gap: 10px; font-family: 'Playfair Display', serif; font-size: 21px; font-weight: 600; }
    .brand-mark { width: 37px; height: 37px; display: grid; place-items: center; color: white; background: var(--green); border-radius: 13px 13px 13px 5px; font-family: 'DM Sans', sans-serif; }
    .brand-name span { color: var(--green); }
    .login-link { color: var(--green-dark); font-size: 12px; font-weight: 700; }
    main { width: min(800px, calc(100% - 32px)); margin: 42px auto 70px; }
    .intro { margin-bottom: 23px; text-align: center; }
    .eyebrow { margin-bottom: 11px; color: var(--green); font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
    h1, h2, p { margin-top: 0; }
    h1 { margin-bottom: 9px; font-family: 'Playfair Display', serif; font-size: clamp(30px, 5vw, 42px); font-weight: 500; }
    .intro-copy { margin-bottom: 0; color: var(--muted); font-size: 12px; line-height: 1.7; }
    .registration-card { padding: clamp(20px, 5vw, 38px); background: #fffefa; border: 1px solid var(--line); border-radius: 16px; box-shadow: 0 20px 60px rgba(46,60,48,.08); }
    .success-message { margin-bottom: 22px; padding: 16px; color: #285d38; background: #edf7ee; border: 1px solid #cce5d0; border-radius: 9px; font-size: 12px; line-height: 1.7; }
    .stepper { display: grid; grid-template-columns: repeat(4, 1fr); margin: 0 0 30px; padding: 0; list-style: none; }
    .step-item { position: relative; display: flex; flex-direction: column; align-items: center; gap: 8px; color: #99a19b; font-size: 9px; font-weight: 600; text-align: center; }
    .step-item:not(:last-child):after { position: absolute; z-index: 0; top: 16px; left: calc(50% + 20px); width: calc(100% - 40px); height: 1px; background: var(--line); content: ''; }
    .step-number { position: relative; z-index: 1; width: 33px; height: 33px; display: grid; place-items: center; background: #f3f5f1; border: 1px solid var(--line); border-radius: 50%; font-size: 11px; }
    .step-item.active, .step-item.complete { color: var(--green-dark); }
    .step-item.active .step-number, .step-item.complete .step-number { color: #fff; background: var(--green-dark); border-color: var(--green-dark); }
    .form-step { display: none; }
    .form-step.active { display: block; animation: appear .18s ease-out; }
    @keyframes appear { from { opacity: .5; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }
    .step-heading { margin-bottom: 20px; }
    .step-heading h2 { margin-bottom: 6px; font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 600; }
    .step-heading p { margin-bottom: 0; color: var(--muted); font-size: 11px; line-height: 1.7; }
    .field-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px; }
    .field { min-width: 0; }
    .field.full { grid-column: 1 / -1; }
    label, .group-label { display: block; margin-bottom: 7px; color: #46564b; font-size: 10px; font-weight: 700; }
    .required { color: var(--error); }
    input, select, textarea { width: 100%; min-height: 43px; padding: 10px 12px; color: var(--ink); background: #fff; border: 1px solid #dfe5dc; border-radius: 7px; font: inherit; font-size: 11px; outline: none; }
    textarea { min-height: 92px; resize: vertical; }
    input:focus, select:focus, textarea:focus { border-color: #78937c; box-shadow: 0 0 0 3px rgba(83,116,93,.1); }
    .field-error { margin: 5px 0 0; color: var(--error); font-size: 10px; }
    .plan-detail { margin-top: 15px; padding: 13px 15px; color: #59665d; background: #f3f7f1; border: 1px solid #e1e9dd; border-radius: 8px; font-size: 10px; line-height: 1.7; }
    .plan-detail strong { display: block; margin-bottom: 4px; color: var(--green-dark); font-size: 10px; }
    .checkbox-row { display: flex; align-items: flex-start; gap: 9px; margin-top: 18px; color: #606c63; font-size: 10px; line-height: 1.7; }
    .checkbox-row input { width: 15px; min-height: 15px; flex: 0 0 15px; margin: 1px 0 0; accent-color: var(--green-dark); }
    .form-actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 25px; padding-top: 20px; border-top: 1px solid var(--line); }
    .button { min-height: 43px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 17px; border: 1px solid transparent; border-radius: 7px; cursor: pointer; font: inherit; font-size: 11px; font-weight: 700; }
    .button-primary { color: white; background: var(--green-dark); }
    .button-primary:hover { background: #243d2f; }
    .button-secondary { color: #526057; background: white; border-color: var(--line); }
    .button:focus-visible { outline: 3px solid rgba(83,116,93,.3); outline-offset: 2px; }
    .review-list { display: grid; gap: 0; border-top: 1px solid var(--line); }
    .review-row { display: grid; grid-template-columns: 150px 1fr; gap: 14px; padding: 12px 0; border-bottom: 1px solid var(--line); font-size: 11px; line-height: 1.6; overflow-wrap: anywhere; }
    .review-row strong { color: #738078; font-size: 10px; }
    .review-empty { color: var(--muted); }
    .privacy-note { margin: 17px 0 0; color: #89938b; font-size: 9px; line-height: 1.6; text-align: center; }
    @media (max-width: 560px) {
      main { margin-top: 28px; }
      .registration-card { padding: 20px 16px; }
      .step-item { font-size: 8px; }
      .step-number { width: 29px; height: 29px; }
      .step-item:not(:last-child):after { top: 14px; left: calc(50% + 17px); width: calc(100% - 34px); }
      .field-grid { grid-template-columns: 1fr; gap: 12px; }
      .field.full { grid-column: auto; }
      .review-row { grid-template-columns: 100px 1fr; gap: 8px; }
    }
  </style>
</head>
<body>
  <header class="topbar">
    <div class="topbar-inner">
      <a class="brand" href="{{ route('home') }}" aria-label="MediCare home">
        <span class="brand-mark"><i class="fas fa-plus"></i></span>
        <span class="brand-name">Medi<span>Care</span></span>
      </a>
      <a class="login-link" href="{{ route('login') }}">Already have an account? Login <i class="fas fa-arrow-right"></i></a>
    </div>
  </header>

  <main>
    <div class="intro">
      <p class="eyebrow">Care partner registration</p>
      <h1>Let’s get your practice connected.</h1>
      <p class="intro-copy">Share a few details so our team can understand your practice and the service you’re interested in.</p>
    </div>

    <section class="registration-card" aria-label="Care partner registration form">
      @if (session('registration_submitted'))
        <div class="success-message" role="status">
          <strong>Registration request received.</strong> The MediCare team will review your details. This request does not create a login account.
        </div>
      @endif

      <ol class="stepper" aria-label="Registration steps">
        <li class="step-item active" data-step-indicator="1"><span class="step-number">1</span><span>Service</span></li>
        <li class="step-item" data-step-indicator="2"><span class="step-number">2</span><span>Practice</span></li>
        <li class="step-item" data-step-indicator="3"><span class="step-number">3</span><span>Contact</span></li>
        <li class="step-item" data-step-indicator="4"><span class="step-number">4</span><span>Review</span></li>
      </ol>

      <form method="POST" action="{{ route('care-partner-registration.store') }}" id="care-partner-registration">
        @csrf
        <section class="form-step active" data-step="1">
          <div class="step-heading">
            <h2>What would you like to register for?</h2>
            <p>Choose the service you’re interested in and tell us what kind of care practice you represent.</p>
          </div>
          <div class="field-grid">
            <div class="field">
              <label for="plan">Service / plan <span class="required">*</span></label>
              <select id="plan" name="plan" required>
                <option value="">Select a service</option>
                @foreach ($plans as $key => $label)
                  <option value="{{ $key }}" @selected(old('plan', $selectedPlan) === $key)>{{ $label }}</option>
                @endforeach
              </select>
              @error('plan')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
              <label for="practice_type">Practice type <span class="required">*</span></label>
              <select id="practice_type" name="practice_type" required>
                <option value="">Select practice type</option>
                <option value="doctor" @selected(old('practice_type') === 'doctor')>Doctor / individual practitioner</option>
                <option value="clinic" @selected(old('practice_type') === 'clinic')>Clinic</option>
                <option value="hospital" @selected(old('practice_type') === 'hospital')>Hospital</option>
                <option value="other" @selected(old('practice_type') === 'other')>Other healthcare provider</option>
              </select>
              @error('practice_type')<p class="field-error">{{ $message }}</p>@enderror
            </div>
          </div>
          <div class="plan-detail" id="plan-detail" aria-live="polite">
            <strong id="plan-detail-title">Plan details</strong>
            <span id="plan-detail-copy">Choose a service to see what is included.</span>
          </div>
        </section>

        <section class="form-step" data-step="2">
          <div class="step-heading">
            <h2>Tell us about your practice.</h2>
            <p>These details help us understand what profile or integration you need.</p>
          </div>
          <div class="field-grid">
            <div class="field">
              <label for="practice_name">Practice / organization name <span class="required">*</span></label>
              <input id="practice_name" name="practice_name" value="{{ old('practice_name') }}" maxlength="160" autocomplete="organization" required>
              @error('practice_name')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
              <label for="specialty">Specialty / main service</label>
              <input id="specialty" name="specialty" value="{{ old('specialty') }}" maxlength="160" placeholder="For example, dental care">
              @error('specialty')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field full">
              <label for="website_url">Current website (if available)</label>
              <input id="website_url" type="url" name="website_url" value="{{ old('website_url') }}" maxlength="2048" placeholder="https://example.com" autocomplete="url">
              @error('website_url')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field full">
              <label for="address">Practice address <span class="required">*</span></label>
              <textarea id="address" name="address" maxlength="2000" autocomplete="street-address" required>{{ old('address') }}</textarea>
              @error('address')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
              <label for="city">City <span class="required">*</span></label>
              <input id="city" name="city" value="{{ old('city') }}" maxlength="120" autocomplete="address-level2" required>
              @error('city')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
              <label for="state">State <span class="required">*</span></label>
              <input id="state" name="state" value="{{ old('state') }}" maxlength="120" autocomplete="address-level1" required>
              @error('state')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
              <label for="postal_code">PIN / postal code <span class="required">*</span></label>
              <input id="postal_code" name="postal_code" value="{{ old('postal_code') }}" maxlength="20" autocomplete="postal-code" required>
              @error('postal_code')<p class="field-error">{{ $message }}</p>@enderror
            </div>
          </div>
        </section>

        <section class="form-step" data-step="3">
          <div class="step-heading">
            <h2>Who should we contact?</h2>
            <p>We’ll use these details only to follow up about this registration request.</p>
          </div>
          <div class="field-grid">
            <div class="field">
              <label for="contact_name">Contact person <span class="required">*</span></label>
              <input id="contact_name" name="contact_name" value="{{ old('contact_name') }}" maxlength="120" autocomplete="name" required>
              @error('contact_name')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field">
              <label for="phone">Phone / WhatsApp <span class="required">*</span></label>
              <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" maxlength="30" autocomplete="tel" required>
              @error('phone')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field full">
              <label for="email">Email address <span class="required">*</span></label>
              <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="191" autocomplete="email" required>
              @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="field full">
              <label for="message">Anything else we should know?</label>
              <textarea id="message" name="message" maxlength="2000" placeholder="Tell us about your requirements, number of branches, or API integration needs.">{{ old('message') }}</textarea>
              @error('message')<p class="field-error">{{ $message }}</p>@enderror
            </div>
          </div>
          <label class="checkbox-row" for="consent">
            <input id="consent" type="checkbox" name="consent" value="1" @checked(old('consent')) required>
            <span>I agree that MediCare may contact me about this registration request. <span class="required">*</span></span>
          </label>
          @error('consent')<p class="field-error">{{ $message }}</p>@enderror
        </section>

        <section class="form-step" data-step="4">
          <div class="step-heading">
            <h2>Review your request.</h2>
            <p>Check the details below, then submit your registration request to the MediCare team.</p>
          </div>
          <div class="review-list">
            <div class="review-row"><strong>Service</strong><span data-review="plan" class="review-empty">Not selected</span></div>
            <div class="review-row"><strong>Practice type</strong><span data-review="practice_type" class="review-empty">Not selected</span></div>
            <div class="review-row"><strong>Practice</strong><span data-review="practice_name" class="review-empty">Not provided</span></div>
            <div class="review-row"><strong>Specialty</strong><span data-review="specialty" class="review-empty">Not provided</span></div>
            <div class="review-row"><strong>Website</strong><span data-review="website_url" class="review-empty">Not provided</span></div>
            <div class="review-row"><strong>Address</strong><span data-review="address" class="review-empty">Not provided</span></div>
            <div class="review-row"><strong>Contact</strong><span data-review="contact_name" class="review-empty">Not provided</span></div>
            <div class="review-row"><strong>Email</strong><span data-review="email" class="review-empty">Not provided</span></div>
            <div class="review-row"><strong>Phone</strong><span data-review="phone" class="review-empty">Not provided</span></div>
            <div class="review-row"><strong>Message</strong><span data-review="message" class="review-empty">Not provided</span></div>
          </div>
        </section>

        <div class="form-actions">
          <button type="button" class="button button-secondary" id="previous-step" hidden><i class="fas fa-arrow-left"></i> Back</button>
          <span id="step-spacer"></span>
          <button type="button" class="button button-primary" id="next-step">Continue <i class="fas fa-arrow-right"></i></button>
          <button type="submit" class="button button-primary" id="submit-registration" hidden>Submit request <i class="fas fa-check"></i></button>
        </div>
      </form>
      <p class="privacy-note">Your details will be used to review and respond to this request. A login account is created separately after approval.</p>
    </section>
  </main>

  <script>
    const form = document.getElementById('care-partner-registration');
    const steps = Array.from(document.querySelectorAll('[data-step]'));
    const indicators = Array.from(document.querySelectorAll('[data-step-indicator]'));
    const previousButton = document.getElementById('previous-step');
    const nextButton = document.getElementById('next-step');
    const submitButton = document.getElementById('submit-registration');
    const spacer = document.getElementById('step-spacer');
    const planLabels = @json($plans);
    const planDetails = @json($planDetails);
    const practiceLabels = {
      doctor: 'Doctor / individual practitioner',
      clinic: 'Clinic',
      hospital: 'Hospital',
      other: 'Other healthcare provider'
    };
    let currentStep = 1;

    function showStep(stepNumber) {
      currentStep = stepNumber;
      steps.forEach((step) => step.classList.toggle('active', Number(step.dataset.step) === stepNumber));
      indicators.forEach((indicator) => {
        const number = Number(indicator.dataset.stepIndicator);
        indicator.classList.toggle('active', number === stepNumber);
        indicator.classList.toggle('complete', number < stepNumber);
        indicator.querySelector('.step-number').innerHTML = number < stepNumber ? '<i class="fas fa-check"></i>' : String(number);
      });
      previousButton.hidden = stepNumber === 1;
      nextButton.hidden = stepNumber === steps.length;
      submitButton.hidden = stepNumber !== steps.length;
      spacer.hidden = stepNumber !== 1;
      if (stepNumber === steps.length) {
        updateReview();
      }
    }

    function updateReview() {
      const fields = ['plan', 'practice_type', 'practice_name', 'specialty', 'website_url', 'address', 'contact_name', 'email', 'phone', 'message'];
      fields.forEach((name) => {
        const target = document.querySelector(`[data-review="${name}"]`);
        const input = form.elements.namedItem(name);
        let value = input ? input.value.trim() : '';
        if (name === 'plan') value = planLabels[value] || '';
        if (name === 'practice_type') value = practiceLabels[value] || '';
        target.textContent = value || (['plan', 'practice_type'].includes(name) ? 'Not selected' : 'Not provided');
        target.classList.toggle('review-empty', !value);
      });
    }

    function updatePlanDetails() {
      const selectedPlan = form.elements.namedItem('plan').value;
      document.getElementById('plan-detail-title').textContent = planLabels[selectedPlan] || 'Plan details';
      document.getElementById('plan-detail-copy').textContent = planDetails[selectedPlan] || 'Choose a service to see what is included.';
    }

    function updateWebsiteOffer() {
      const selectedPlan = form.elements.namedItem('plan').value;
      document.getElementById('website-offer').hidden = !['website', 'website_listing'].includes(selectedPlan);
    }

    nextButton.addEventListener('click', () => {
      const currentPanel = steps[currentStep - 1];
      const invalidField = Array.from(currentPanel.querySelectorAll('input, select, textarea'))
        .find((field) => !field.checkValidity());
      if (invalidField) {
        invalidField.reportValidity();
        return;
      }
      showStep(Math.min(currentStep + 1, steps.length));
    });

    previousButton.addEventListener('click', () => showStep(Math.max(currentStep - 1, 1)));
    form.addEventListener('input', updateReview);
    form.addEventListener('change', updateReview);
    form.elements.namedItem('plan').addEventListener('change', () => {
      updatePlanDetails();
      updateWebsiteOffer();
    });

    const errorField = @json($errors->keys()[0] ?? null);
    const errorSteps = {
      plan: 1,
      practice_type: 1,
      practice_name: 2,
      specialty: 2,
      website_url: 2,
      address: 2,
      city: 2,
      state: 2,
      postal_code: 2,
      contact_name: 3,
      email: 3,
      phone: 3,
      message: 3,
      consent: 3
    };
    updatePlanDetails();
    updateWebsiteOffer();
    showStep(errorSteps[errorField] || 1);
  </script>
</body>
</html>
