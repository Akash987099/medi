<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f6f7f2">
  <meta name="description" content="Discover care services and connect with doctors and hospitals through MediCare.">
  <title>MediCare | Care, connected</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      color-scheme: light;
      --ink: #26382f;
      --muted: #748078;
      --line: #e6e9e1;
      --paper: #f6f7f2;
      --white: #fffefa;
      --green: #53745d;
      --green-dark: #304b3b;
      --sage: #e7eee5;
      --gold: #c5a16b;
      --shadow: 0 24px 70px rgba(46, 60, 48, .09);
    }
    * { box-sizing: border-box; }
    html { scroll-behavior: smooth; }
    [id] { scroll-margin-top: 105px; }
    body { margin: 0; color: var(--ink); background: var(--paper); font-family: 'DM Sans', sans-serif; -webkit-font-smoothing: antialiased; }
    a { color: inherit; text-decoration: none; }
    button { font: inherit; }
    .site-header { position: sticky; z-index: 5; top: 0; background: rgba(255,254,250,.94); border-bottom: 1px solid rgba(230,233,225,.85); backdrop-filter: blur(14px); }
    .header-inner { width: min(1180px, calc(100% - 48px)); min-height: 78px; display: flex; align-items: center; justify-content: space-between; gap: 24px; margin: auto; }
    .brand { display: inline-flex; align-items: center; gap: 11px; font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 600; letter-spacing: -.5px; }
    .brand-mark { width: 40px; height: 40px; display: grid; place-items: center; color: #fff; background: var(--green); border-radius: 14px 14px 14px 5px; box-shadow: 0 6px 15px rgba(84,116,93,.19); font-family: 'DM Sans', sans-serif; font-size: 17px; }
    .brand-name span { color: var(--green); }
    .main-nav { display: flex; align-items: center; gap: 34px; color: #68746b; font-size: 12px; font-weight: 600; }
    .main-nav a:hover { color: var(--green-dark); }
    .header-login { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 9px; padding: 0 17px; color: #fff; background: var(--green-dark); border: 1px solid var(--green-dark); border-radius: 8px; font-size: 12px; font-weight: 700; transition: background .18s ease, transform .18s ease; }
    .header-login:hover, .button-primary:hover { background: #243d2f; transform: translateY(-1px); }
    .header-actions { display: flex; align-items: center; gap: 9px; }
    .header-register { min-height: 42px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 15px; color: var(--green-dark); background: transparent; border: 1px solid var(--line); border-radius: 8px; font-size: 12px; font-weight: 700; transition: background .18s ease, transform .18s ease; }
    .header-register:hover { background: #fff; transform: translateY(-1px); }
    .hero { position: relative; overflow: hidden; padding: 76px 0 72px; background: radial-gradient(ellipse at 82% 48%, rgba(214,228,211,.7), transparent 38%), linear-gradient(180deg, #fffefa 0%, #f6f7f2 100%); }
    .hero:before { position: absolute; top: -190px; right: -120px; width: 580px; height: 580px; border: 1px solid rgba(83,116,93,.1); border-radius: 50%; box-shadow: 0 0 0 38px rgba(83,116,93,.025), 0 0 0 78px rgba(83,116,93,.02); content: ''; pointer-events: none; }
    .hero-inner { position: relative; width: min(1180px, calc(100% - 48px)); display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(360px, .95fr); align-items: center; gap: 48px; margin: auto; }
    .eyebrow { display: inline-flex; align-items: center; gap: 9px; margin: 0 0 21px; color: var(--green); font-size: 10px; font-weight: 700; letter-spacing: 1.7px; text-transform: uppercase; }
    .eyebrow:before { width: 22px; height: 1px; background: var(--gold); content: ''; }
    h1, h2, h3, p { margin-top: 0; }
    .hero h1 { max-width: 630px; margin-bottom: 20px; font-family: 'Playfair Display', serif; font-size: clamp(44px, 6vw, 72px); font-weight: 500; letter-spacing: -2.7px; line-height: 1.04; }
    .hero h1 em { color: var(--green); font-weight: 500; }
    .hero-copy { max-width: 500px; margin-bottom: 28px; color: var(--muted); font-size: 15px; line-height: 1.85; }
    .hero-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 13px; }
    .button-primary, .button-secondary { min-height: 48px; display: inline-flex; align-items: center; justify-content: center; gap: 10px; padding: 0 19px; border-radius: 8px; font-size: 12px; font-weight: 700; transition: background .18s ease, transform .18s ease; }
    .button-primary { color: #fff; background: var(--green-dark); }
    .button-secondary { color: var(--ink); background: rgba(255,255,255,.62); border: 1px solid var(--line); }
    .button-secondary:hover { background: #fff; }
    .hero-note { display: flex; align-items: center; gap: 9px; margin-top: 25px; color: #8a938c; font-size: 11px; }
    .hero-note i { color: var(--green); }
    .launch-offer { padding: 27px 0; background: var(--green-dark); color: #fff; }
    .launch-offer-inner { width: min(1100px, calc(100% - 48px)); display: grid; grid-template-columns: minmax(190px, .75fr) 2fr; align-items: center; gap: 27px; margin: auto; }
    .launch-offer .eyebrow { margin-bottom: 8px; color: #c9dbc6; }
    .launch-offer h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 500; line-height: 1.25; }
    .offer-content p { margin: 0 0 13px; color: rgba(255,255,255,.72); font-size: 10px; line-height: 1.65; }
    .offer-benefit-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; }
    .offer-benefit { display: flex; align-items: flex-start; gap: 8px; color: #fff; font-size: 10px; line-height: 1.6; }
    .offer-benefit i { margin-top: 2px; color: #c9dbc6; }
    .offer-terms { margin: 11px 0 0 !important; color: rgba(255,255,255,.58) !important; font-size: 9px !important; }
    .hero-art { position: relative; min-height: 440px; display: grid; place-items: center; }
    .art-orbit { position: absolute; width: 390px; height: 390px; border: 1px solid rgba(83,116,93,.16); border-radius: 50%; }
    .art-orbit:before, .art-orbit:after { position: absolute; border: 1px solid rgba(83,116,93,.12); border-radius: 50%; content: ''; }
    .art-orbit:before { inset: 25px; }
    .art-orbit:after { inset: 54px; }
    .art-glow { position: absolute; width: 275px; height: 275px; background: #e4ede1; border-radius: 50%; }
    .medical-card { position: relative; z-index: 1; width: min(100%, 350px); min-height: 310px; display: flex; flex-direction: column; justify-content: space-between; padding: 27px; background: var(--white); border: 1px solid rgba(230,233,225,.9); border-radius: 19px; box-shadow: var(--shadow); transform: rotate(-2deg); }
    .medical-card-top { display: flex; align-items: center; justify-content: space-between; }
    .medical-symbol { width: 51px; height: 51px; display: grid; place-items: center; color: var(--green-dark); background: var(--sage); border-radius: 15px; font-size: 20px; }
    .open-label { display: inline-flex; align-items: center; gap: 7px; padding: 7px 10px; color: #47704f; background: #edf4eb; border-radius: 30px; font-size: 9px; font-weight: 700; }
    .open-label span { width: 6px; height: 6px; background: #70a175; border-radius: 50%; }
    .card-label { margin-bottom: 8px; color: #89938b; font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
    .medical-card h2 { margin-bottom: 9px; font-family: 'Playfair Display', serif; font-size: 27px; font-weight: 600; }
    .medical-card p { margin-bottom: 0; color: var(--muted); font-size: 11px; line-height: 1.7; }
    .card-divider { height: 1px; margin: 18px 0; background: var(--line); }
    .card-footer { display: flex; align-items: center; gap: 11px; }
    .avatar-stack { display: flex; padding-left: 4px; }
    .avatar-stack span { width: 28px; height: 28px; display: grid; place-items: center; margin-left: -4px; color: var(--green-dark); background: #dfe9dd; border: 2px solid #fffefa; border-radius: 50%; font-size: 9px; }
    .avatar-stack span:nth-child(2) { background: #eee3d2; }
    .avatar-stack span:nth-child(3) { background: #e1e7eb; }
    .card-footer small { color: #7d8880; font-size: 10px; }
    .floating-note { position: absolute; z-index: 2; display: flex; align-items: center; gap: 10px; padding: 12px 14px; background: #fffefa; border: 1px solid var(--line); border-radius: 11px; box-shadow: 0 12px 35px rgba(46,60,48,.08); }
    .floating-note i { color: var(--green); }
    .floating-note strong, .floating-note span { display: block; }
    .floating-note strong { margin-bottom: 3px; font-size: 10px; }
    .floating-note span { color: var(--muted); font-size: 9px; }
    .floating-note.one { top: 53px; right: -1px; }
    .floating-note.two { bottom: 42px; left: -5px; }
    .section { padding: 82px 0; }
    .section-inner { width: min(1100px, calc(100% - 48px)); margin: auto; }
    .section-heading { max-width: 610px; margin: 0 auto 37px; text-align: center; }
    .section-heading .eyebrow { justify-content: center; }
    .section-heading h2 { margin-bottom: 12px; font-family: 'Playfair Display', serif; font-size: clamp(31px, 4vw, 42px); font-weight: 500; letter-spacing: -1px; }
    .section-heading p { margin-bottom: 0; color: var(--muted); font-size: 13px; line-height: 1.8; }
    .network-section { padding: 78px 0; background: #f0f4ed; border-top: 1px solid #e3e9e0; border-bottom: 1px solid #e3e9e0; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 43px; }
    .stat-card { min-height: 105px; padding: 18px; background: #fffefa; border: 1px solid var(--line); border-radius: 11px; }
    .stat-card strong { display: block; margin-bottom: 5px; color: var(--green-dark); font-family: 'Playfair Display', serif; font-size: 27px; font-weight: 600; }
    .stat-card span { color: var(--muted); font-size: 10px; }
    .directory-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
    .directory-heading h3 { margin: 0; font-family: 'Playfair Display', serif; font-size: 22px; font-weight: 600; }
    .directory-heading a { color: var(--green-dark); font-size: 10px; font-weight: 700; }
    .directory-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
    .directory-card { padding: 20px; background: #fffefa; border: 1px solid var(--line); border-radius: 12px; }
    .directory-card-top { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .directory-icon { width: 43px; height: 43px; display: grid; flex: 0 0 43px; place-items: center; color: var(--green); background: var(--sage); border-radius: 12px; }
    .directory-type { display: block; margin-bottom: 4px; color: #8a938c; font-size: 8px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
    .directory-card h4 { margin: 0; font-size: 12px; font-weight: 700; }
    .directory-card p { margin: 0 0 15px; color: var(--muted); font-size: 10px; line-height: 1.7; }
    .directory-meta { display: flex; align-items: center; gap: 7px; color: #69756c; font-size: 9px; }
    .directory-meta i { color: var(--green); }
    .directory-empty { grid-column: 1 / -1; padding: 24px; color: var(--muted); background: #fffefa; border: 1px solid var(--line); border-radius: 12px; font-size: 11px; line-height: 1.7; text-align: center; }
    .feature-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 17px; }
    .feature-card { min-height: 217px; padding: 25px; background: var(--white); border: 1px solid var(--line); border-radius: 13px; transition: transform .18s ease, box-shadow .18s ease; }
    .feature-card:hover { transform: translateY(-4px); box-shadow: 0 14px 34px rgba(46,60,48,.07); }
    .feature-icon { width: 43px; height: 43px; display: grid; place-items: center; margin-bottom: 20px; color: var(--green); background: var(--sage); border-radius: 12px; font-size: 16px; }
    .feature-card h3 { margin-bottom: 8px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
    .feature-card p { margin-bottom: 0; color: var(--muted); font-size: 11px; line-height: 1.8; }
    .platform-section { padding: 78px 0; background: #f0f4ed; border-top: 1px solid #e3e9e0; border-bottom: 1px solid #e3e9e0; }
    .platform-section .feature-card { min-height: 195px; }
    .platform-note { max-width: 760px; margin: 20px auto 0; padding: 12px 15px; color: #647168; background: #fffefa; border: 1px solid var(--line); border-radius: 9px; font-size: 10px; line-height: 1.7; text-align: center; }
    .platform-cta { margin-top: 22px; text-align: center; }
    .pricing-section { padding: 78px 0; background: #fffefa; border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
    .pricing-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; align-items: stretch; }
    .pricing-card { position: relative; display: flex; flex-direction: column; min-width: 0; padding: 23px 20px 20px; background: #fff; border: 1px solid var(--line); border-radius: 13px; }
    .pricing-card.selected { background: #f5f8f2; border-color: #9fb49d; box-shadow: 0 12px 35px rgba(46,60,48,.12); }
    .pricing-card[role="button"] { cursor: pointer; transition: background .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .pricing-card[role="button"]:focus-visible { outline: 3px solid rgba(83,116,93,.35); outline-offset: 3px; }
    .pricing-tag { min-height: 15px; margin-bottom: 12px; color: var(--green); font-size: 9px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
    .pricing-card h3 { min-height: 48px; margin-bottom: 9px; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; line-height: 1.25; }
    .pricing-description { min-height: 57px; margin-bottom: 17px; color: var(--muted); font-size: 10px; line-height: 1.7; }
    .pricing-amount { margin-bottom: 5px; color: var(--green-dark); font-family: 'Playfair Display', serif; font-size: 25px; font-weight: 600; line-height: 1.2; }
    .pricing-period { min-height: 17px; margin-bottom: 17px; color: #8a938c; font-size: 9px; }
    .pricing-divider { height: 1px; margin-bottom: 15px; background: var(--line); }
    .pricing-features { display: grid; gap: 10px; margin: 0 0 21px; padding: 0; list-style: none; }
    .pricing-features li { display: flex; align-items: flex-start; gap: 8px; color: #59665d; font-size: 10px; line-height: 1.6; }
    .pricing-features i { margin-top: 2px; color: var(--green); font-size: 10px; }
    .pricing-action { width: 100%; min-height: 41px; display: inline-flex; align-items: center; justify-content: center; gap: 8px; margin-top: auto; padding: 0 11px; color: #fff; background: var(--green-dark); border: 1px solid var(--green-dark); border-radius: 7px; font-size: 10px; font-weight: 700; transition: background .18s ease, transform .18s ease; }
    .pricing-action:hover { background: #243d2f; transform: translateY(-1px); }
    .pricing-note { margin: 21px 0 0; color: #879188; font-size: 10px; line-height: 1.7; text-align: center; }
    .pricing-note a { color: var(--green-dark); font-weight: 700; text-decoration: underline; text-underline-offset: 3px; }
    .api-flow { margin-top: 28px; padding: 20px 22px; background: #f4f7f1; border: 1px solid #e1e9dd; border-radius: 11px; }
    .api-flow h3 { margin-bottom: 8px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
    .api-flow p { margin: 0; color: var(--muted); font-size: 11px; line-height: 1.8; }
    .about-section { padding: 0 0 82px; }
    .about-panel { display: grid; grid-template-columns: .9fr 1.1fr; overflow: hidden; background: var(--green-dark); border-radius: 17px; }
    .about-visual { position: relative; min-height: 300px; display: grid; place-items: center; overflow: hidden; background: linear-gradient(145deg, #718b73, #3e6048); }
    .about-visual:before, .about-visual:after { position: absolute; width: 220px; height: 220px; border: 1px solid rgba(255,255,255,.2); border-radius: 50%; content: ''; }
    .about-visual:after { width: 300px; height: 300px; }
    .about-cross { position: relative; z-index: 1; width: 108px; height: 108px; display: grid; place-items: center; color: #fff; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.35); border-radius: 31px 31px 31px 10px; font-size: 42px; backdrop-filter: blur(7px); }
    .about-copy { align-self: center; padding: 45px clamp(28px, 5vw, 58px); color: #fff; }
    .about-copy .eyebrow { color: #c9dbc6; }
    .about-copy h2 { margin-bottom: 14px; font-family: 'Playfair Display', serif; font-size: clamp(29px, 4vw, 39px); font-weight: 500; line-height: 1.2; }
    .about-copy p { margin-bottom: 22px; color: rgba(255,255,255,.73); font-size: 12px; line-height: 1.85; }
    .about-copy .button-secondary { color: var(--green-dark); background: #fffefa; border-color: #fffefa; }
    .contact-section { padding: 78px 0; background: #f6f7f2; border-top: 1px solid var(--line); }
    .contact-grid { display: grid; grid-template-columns: minmax(260px, .78fr) minmax(0, 1.22fr); align-items: stretch; gap: 20px; }
    .contact-card { padding: 30px; background: #fffefa; border: 1px solid var(--line); border-radius: 15px; box-shadow: 0 12px 35px rgba(46,60,48,.045); }
    .contact-card h3 { margin-bottom: 8px; font-family: 'Playfair Display', serif; font-size: 21px; font-weight: 600; }
    .contact-card > p { margin-bottom: 20px; color: var(--muted); font-size: 11px; line-height: 1.8; }
    .contact-detail { display: flex; align-items: flex-start; gap: 13px; padding: 15px 0; border-top: 1px solid var(--line); }
    .contact-detail i { width: 34px; height: 34px; flex: 0 0 34px; display: grid; place-items: center; margin-top: 1px; color: var(--green); background: var(--sage); border-radius: 10px; }
    .contact-detail strong, .contact-detail span { display: block; }
    .contact-detail strong { margin-bottom: 4px; font-size: 10px; }
    .contact-detail span { color: var(--muted); font-size: 10px; line-height: 1.6; }
    .contact-quick-links { display: grid; gap: 8px; margin-top: auto; padding-top: 20px; }
    .contact-quick-links > p { margin: 0 0 3px; color: var(--muted); font-size: 10px; font-weight: 600; }
    .contact-quick-link { min-height: 40px; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 0 12px; color: var(--green-dark); background: #f6f8f3; border: 1px solid #e6ebe3; border-radius: 8px; font-size: 10px; font-weight: 700; transition: background .18s ease, border-color .18s ease; }
    .contact-quick-link:hover { background: #edf3eb; border-color: #d7e2d5; }
    .contact-quick-link.secondary { min-height: 34px; justify-content: center; background: transparent; border-color: transparent; }
    .contact-quick-link.secondary:hover { background: #f6f8f3; }
    .contact-form { display: grid; gap: 14px; }
    .contact-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 15px 14px; }
    .contact-form-field { display: grid; gap: 6px; }
    .contact-form-field.wide { grid-column: 1 / -1; }
    .contact-form-field label { color: #526255; font-size: 11px; font-weight: 700; }
    .contact-form-field input, .contact-form-field textarea { width: 100%; min-height: 46px; padding: 12px 13px; color: var(--ink); background: #fafbf8; border: 1px solid #e2e7df; border-radius: 8px; outline: none; font: inherit; font-size: 12px; transition: background .18s ease, border-color .18s ease, box-shadow .18s ease; }
    .contact-form-field textarea { min-height: 126px; resize: vertical; }
    .contact-form-field input:focus, .contact-form-field textarea:focus { background: #fff; border-color: #91a58f; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
    .contact-form-submit { min-height: 46px; justify-self: start; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 18px; color: #fff; background: var(--green-dark); border: 1px solid var(--green-dark); border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 700; transition: background .18s ease, transform .18s ease; }
    .contact-form-submit:hover { background: #243d2f; transform: translateY(-1px); }
    .contact-form-note { margin: 0; color: var(--muted); font-size: 10px; line-height: 1.6; }
    .contact-form-alert { margin-bottom: 15px; padding: 13px 16px; color: #4c6a50; background: #eff5ed; border: 1px solid #dce8d9; border-radius: 9px; font-size: 12px; }
    .contact-form-alert.error { color: #8d5544; background: #fbefeb; border-color: #f0d9ce; }
    .branch-section { padding: 64px 0 78px; background: #fffefa; border-top: 1px solid var(--line); }
    .branch-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .branch-card { padding: 23px; background: #f7f8f4; border: 1px solid var(--line); border-radius: 13px; transition: transform .18s ease, box-shadow .18s ease; }
    .branch-card:hover { transform: translateY(-2px); box-shadow: 0 12px 28px rgba(46,60,48,.06); }
    .branch-card-heading { display: flex; align-items: center; gap: 11px; margin-bottom: 12px; }
    .branch-icon { width: 38px; height: 38px; display: grid; place-items: center; color: var(--green); background: var(--sage); border-radius: 10px; }
    .branch-card h3 { margin: 0; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
    .branch-demo-label { display: inline-flex; margin-top: 4px; color: #9a6b45; font-size: 9px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; }
    .branch-address { margin: 0; color: var(--muted); font-size: 11px; line-height: 1.7; }
    .branch-disclaimer { margin: 16px 0 0; color: #927d62; font-size: 10px; line-height: 1.6; text-align: center; }
    .contact-form-card { padding: 30px; background: #fffefa; border: 1px solid var(--line); border-radius: 15px; color: var(--ink); box-shadow: 0 12px 35px rgba(46,60,48,.055); }
    .contact-form-card .eyebrow { margin-bottom: 13px; }
    .contact-form-card h3 { margin-bottom: 22px; font-family: 'Playfair Display', serif; font-size: 29px; font-weight: 500; line-height: 1.25; }
    .site-footer { padding: 25px 0; background: #fffefa; border-top: 1px solid var(--line); }
    .footer-inner { width: min(1180px, calc(100% - 48px)); display: flex; align-items: center; justify-content: space-between; gap: 16px; margin: auto; color: var(--muted); font-size: 10px; }
    .footer-inner p { margin: 0; }
    .footer-links { display: flex; gap: 20px; }
    .footer-links a:hover { color: var(--green-dark); }
    @media (max-width: 850px) {
      .hero-inner { grid-template-columns: 1fr .85fr; gap: 22px; }
      .hero-art { min-height: 385px; }
      .art-orbit { width: 320px; height: 320px; }
      .art-glow { width: 225px; height: 225px; }
      .medical-card { width: 310px; min-height: 285px; padding: 22px; }
      .floating-note.one { right: -10px; }
      .feature-grid { gap: 11px; }
      .feature-card { padding: 20px; }
      .pricing-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .stats-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
      .launch-offer-inner { grid-template-columns: 1fr; gap: 16px; }
      .offer-benefit-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .contact-grid { grid-template-columns: minmax(220px, .8fr) minmax(0, 1.2fr); gap: 14px; }
      .contact-card, .contact-form-card { padding: 23px; }
    }
    @media (max-width: 650px) {
      .header-inner { width: min(100% - 32px, 1180px); min-height: 68px; gap: 12px; }
      .brand { gap: 8px; font-size: 19px; }
      .brand-mark { width: 36px; height: 36px; }
      .header-inner { flex-wrap: wrap; padding: 10px 0; }
      .main-nav { width: 100%; order: 3; display: flex; justify-content: space-between; gap: 14px; overflow-x: auto; padding: 3px 0 5px; font-size: 10px; }
      .main-nav a { flex: 0 0 auto; }
      .header-login { min-height: 39px; padding: 0 12px; font-size: 11px; }
      .hero { padding: 54px 0 42px; }
      .launch-offer-inner { width: min(100% - 36px, 500px); }
      .launch-offer h2 { font-size: 22px; }
      .hero-inner { width: min(100% - 36px, 500px); grid-template-columns: 1fr; gap: 7px; }
      .hero h1 { font-size: clamp(43px, 12vw, 61px); letter-spacing: -2px; }
      .hero-copy { font-size: 13px; }
      .hero-art { min-height: 360px; margin-top: 3px; }
      .art-orbit { width: 300px; height: 300px; }
      .art-glow { width: 215px; height: 215px; }
      .medical-card { width: min(300px, calc(100% - 34px)); min-height: 270px; }
      .floating-note.one { top: 25px; right: 0; }
      .floating-note.two { bottom: 15px; left: 0; }
      .section { padding: 60px 0; }
      .section-inner { width: min(100% - 36px, 500px); }
      .section-heading { margin-bottom: 25px; }
      .section-heading h2 { font-size: 32px; }
      .feature-grid { grid-template-columns: 1fr; }
      .feature-card { min-height: auto; padding: 21px; }
      .platform-section { padding: 60px 0; }
      .network-section { padding: 60px 0; }
      .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; margin-bottom: 32px; }
      .stat-card { min-height: 90px; padding: 15px; }
      .stat-card strong { font-size: 23px; }
      .directory-heading { align-items: flex-start; flex-direction: column; }
      .directory-grid { grid-template-columns: 1fr; }
      .pricing-section { padding: 60px 0; }
      .pricing-grid { grid-template-columns: 1fr; }
      .pricing-card { padding: 22px; }
      .pricing-card h3, .pricing-description { min-height: 0; }
      .pricing-period { min-height: 0; }
      .api-flow { padding: 18px; }
      .feature-icon { margin-bottom: 14px; }
      .about-section { padding-bottom: 60px; }
      .about-panel { grid-template-columns: 1fr; }
      .about-visual { min-height: 215px; }
      .about-copy { padding: 31px 25px; }
      .contact-section { padding: 60px 0; }
      .contact-grid { grid-template-columns: 1fr; }
      .contact-card, .contact-form-card { padding: 23px; }
      .branch-grid { grid-template-columns: 1fr; }
      .contact-form-card h3 { font-size: 24px; }
      .contact-form-grid { grid-template-columns: 1fr; }
      .contact-form-field.wide { grid-column: auto; }
      .footer-inner { width: min(100% - 36px, 500px); flex-direction: column; align-items: flex-start; }
      .footer-links { flex-wrap: wrap; gap: 14px; }
    }
    @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } *, *:before, *:after { transition-duration: .01ms !important; } }
  </style>
</head>
<body>
  <header class="site-header">
    <div class="header-inner">
      <a class="brand" href="{{ route('home') }}" aria-label="MediCare home">
        <span class="brand-mark"><i class="fas fa-plus"></i></span>
        <span class="brand-name">Medi<span>Care</span></span>
      </a>
      <nav class="main-nav" aria-label="Main navigation">
        <a href="#home">Home</a>
        <a href="#services">Services</a>
        <a href="#directory">Listings</a>
        <a href="#plans">Plans</a>
        <a href="#contact">Contact</a>
      </nav>
      <div class="header-actions">
        <a class="header-register" href="{{ route('care-partner-registration.create') }}"><i class="fas fa-user-plus"></i> Register</a>
        <a class="header-login" href="{{ route('login') }}"><i class="fas fa-sign-in-alt"></i> Login</a>
      </div>
    </div>
  </header>

  <main>
    <section class="hero" id="home">
      <div class="hero-inner">
        <div class="hero-content">
          <p class="eyebrow">Healthcare, made more connected</p>
          <h1>Care that feels <em>closer.</em></h1>
          <p class="hero-copy">A welcoming place to discover care services and connect with the doctors and hospitals behind them.</p>
          <div class="hero-actions">
            <a class="button-primary" href="#directory">Explore listings <i class="fas fa-arrow-right"></i></a>
            <a class="button-secondary" href="{{ route('care-partner-registration.create') }}">Register your practice <i class="fas fa-user-plus"></i></a>
          </div>
          <div class="hero-note"><i class="fas fa-heart"></i> Thoughtful care starts with a connected team.</div>
        </div>

        <div class="hero-art" aria-label="MediCare connecting care teams">
          <div class="art-orbit" aria-hidden="true"></div>
          <div class="art-glow" aria-hidden="true"></div>
          <div class="floating-note one"><i class="fas fa-shield-alt"></i><div><strong>Care partner workspace</strong><span>One place for your practice</span></div></div>
          <article class="medical-card">
            <div class="medical-card-top">
              <span class="medical-symbol"><i class="fas fa-heartbeat"></i></span>
              <span class="open-label"><span></span> Connected care</span>
            </div>
            <div>
              <p class="card-label">MediCare network</p>
              <h2>People first.<br>Care always.</h2>
              <p>Bringing care teams and the services they provide into one welcoming experience.</p>
            </div>
            <div>
              <div class="card-divider"></div>
              <div class="card-footer">
                <span class="avatar-stack" aria-hidden="true"><span><i class="fas fa-user-md"></i></span><span><i class="fas fa-hospital"></i></span><span><i class="fas fa-heart"></i></span></span>
                <small>Doctors · Hospitals · Services</small>
              </div>
            </div>
          </article>
          <div class="floating-note two"><i class="fas fa-hand-holding-medical"></i><div><strong>Care, connected</strong><span>Built around your healthcare team</span></div></div>
        </div>
      </div>
    </section>

    <section class="launch-offer" aria-labelledby="launch-offer-title">
      <div class="launch-offer-inner">
        <div>
          <p class="eyebrow">Website launch offer</p>
          <h2 id="launch-offer-title">Start your practice website with more included.</h2>
        </div>
        <div class="offer-content">
          <p>Available with Website only and Website + MediCare listing plans.</p>
          <div class="offer-benefit-grid">
            <div class="offer-benefit"><i class="fas fa-tag"></i><span>20% off eligible domain registration for a limited time</span></div>
            <div class="offer-benefit"><i class="fas fa-server"></i><span>Website hosting included at no charge for the first 6 months</span></div>
            <div class="offer-benefit"><i class="fas fa-laptop-medical"></i><span>Website management included at no charge for the first 3 months</span></div>
            <div class="offer-benefit"><i class="fas fa-headset"></i><span>24/7 support for website and hosting assistance</span></div>
          </div>
          <p class="offer-terms">Offer applies to eligible website plans and domain extensions. Domain availability, discounted price, hosting specifications, support scope, and charges after the included periods will be confirmed before you approve the order. Domain renewals and ongoing services are charged at the then-current rates.</p>
        </div>
      </div>
    </section>

    <section class="section" id="services">
      <div class="section-inner">
        <div class="section-heading">
          <p class="eyebrow">A simpler way to connect</p>
          <h2>Care teams, working together.</h2>
          <p>Discover a connected healthcare experience designed to bring practices, services, and patient information together.</p>
        </div>
        <div class="feature-grid">
          <article class="feature-card">
            <span class="feature-icon"><i class="fas fa-user-md"></i></span>
            <h3>Meet care professionals</h3>
            <p>Find doctors and care partners, with information about their specialties and practices.</p>
          </article>
          <article class="feature-card">
            <span class="feature-icon"><i class="fas fa-hospital"></i></span>
            <h3>Explore hospitals</h3>
            <p>Get to know participating hospitals and the care teams working within them.</p>
          </article>
          <article class="feature-card">
            <span class="feature-icon"><i class="fas fa-heartbeat"></i></span>
            <h3>Discover services</h3>
            <p>Browse care services and packages shared by healthcare providers.</p>
          </article>
        </div>
      </div>
    </section>

    <section class="platform-section" id="practice-tools">
      <div class="section-inner">
        <div class="section-heading">
          <p class="eyebrow">Your practice, one workspace</p>
          <h2>Manage your digital presence from the panel.</h2>
          <p>Keep key practice links, media, and communication settings organized in your MediCare workspace.</p>
        </div>
        <div class="feature-grid">
          <article class="feature-card">
            <span class="feature-icon"><i class="fas fa-share-alt"></i></span>
            <h3>Social profile links</h3>
            <p>Add and update your practice’s Instagram, Facebook, YouTube, WhatsApp, and other profile links from one place.</p>
          </article>
          <article class="feature-card">
            <span class="feature-icon"><i class="fab fa-youtube"></i></span>
            <h3>YouTube videos</h3>
            <p>Add YouTube videos to your practice website, manage video titles and captions, and organize uploaded media.</p>
          </article>
          <article class="feature-card">
            <span class="feature-icon"><i class="fas fa-envelope-open-text"></i></span>
            <h3>Email configuration</h3>
            <p>Set up your own SMTP details in the panel and send a test email to check that your mail configuration works.</p>
          </article>
          <article class="feature-card">
            <span class="feature-icon"><i class="fab fa-whatsapp"></i></span>
            <h3>WhatsApp contact</h3>
            <p>Save your practice’s WhatsApp number and make it easy for visitors to find the right contact channel.</p>
          </article>
          <article class="feature-card">
            <span class="feature-icon"><i class="fas fa-images"></i></span>
            <h3>Practice content</h3>
            <p>Manage your services, packages, gallery, news, articles, FAQs, and website content from the same workspace.</p>
          </article>
          <article class="feature-card">
            <span class="feature-icon"><i class="fas fa-globe"></i></span>
            <h3>Website and API options</h3>
            <p>Choose a MediCare listing, a dedicated practice website, or a domain-bound API connection for a website you already have.</p>
          </article>
          <article class="feature-card">
            <span class="feature-icon"><i class="far fa-calendar-check"></i></span>
            <h3>Appointment management</h3>
            <p>Schedule patient visits in your private panel with patient contact details, visit reason, appointment duration, and team notes.</p>
          </article>
          <article class="feature-card">
            <span class="feature-icon"><i class="fas fa-user-md"></i></span>
            <h3>Coordinate your care team</h3>
            <p>Hospital teams can assign visits to their active doctors and avoid overlapping scheduled or confirmed appointments for the same doctor.</p>
          </article>
          <article class="feature-card">
            <span class="feature-icon"><i class="fas fa-tasks"></i></span>
            <h3>Track every visit</h3>
            <p>Update appointment status from scheduled to confirmed, completed, cancelled, or no-show, then search and filter your appointment register.</p>
          </article>
        </div>
        <p class="platform-note">Appointments are managed by signed-in care partners in their private workspace; patients cannot book online through this panel yet. Email campaigns to mailing lists and bulk WhatsApp broadcasts are not currently included. Email and WhatsApp features depend on your own provider accounts, settings, and applicable platform rules.</p>
        <div class="platform-cta"><a class="button-primary" href="{{ route('login') }}">Login to your panel <i class="fas fa-arrow-right"></i></a></div>
      </div>
    </section>

    <section class="network-section" id="directory">
      <div class="section-inner">
        <div class="section-heading">
          <p class="eyebrow">MediCare network</p>
          <h2>Care, across communities.</h2>
          <p>Explore active care partners and services connected through MediCare.</p>
        </div>

        <div class="stats-grid" aria-label="MediCare network statistics">
          @foreach ($stats as $stat)
            <article class="stat-card"><strong>{{ number_format($stat['value']) }}</strong><span>{{ $stat['label'] }}</span></article>
          @endforeach
        </div>

        <div class="directory-heading">
          <h3>Care partners</h3>
          <a href="{{ route('care-partner-registration.create') }}">Add your practice <i class="fas fa-arrow-right"></i></a>
        </div>
        <div class="directory-grid">
          @foreach ($doctors as $doctor)
            <article class="directory-card">
              <div class="directory-card-top">
                <span class="directory-icon"><i class="fas fa-user-md"></i></span>
                <div><span class="directory-type">Doctor</span><h4>{{ $doctor->name }}</h4></div>
              </div>
              <p>{{ $doctor->qualification }}@if ($doctor->category) · {{ $doctor->category->name }}@endif</p>
              <span class="directory-meta"><i class="fas fa-user-md"></i> Active doctor</span>
            </article>
          @endforeach
          @foreach ($hospitals as $hospital)
            <article class="directory-card">
              <div class="directory-card-top">
                <span class="directory-icon"><i class="fas fa-hospital"></i></span>
                <div><span class="directory-type">Hospital</span><h4>{{ $hospital->name }}</h4></div>
              </div>
              <p>{{ $hospital->bio ?: 'Healthcare services and care teams.' }}</p>
              <span class="directory-meta"><i class="fas fa-hospital"></i> Active hospital</span>
            </article>
          @endforeach
          @if ($doctors->isEmpty() && $hospitals->isEmpty())
            <p class="directory-empty">Care partner profiles will appear here as they become available.</p>
          @endif
        </div>
      </div>
    </section>

    <section class="pricing-section" id="plans">
      <div class="section-inner">
        <div class="section-heading">
          <p class="eyebrow">Flexible ways to work together</p>
          <h2>Choose what your practice needs.</h2>
          <p>Start with a MediCare listing, get a dedicated website, combine both, or connect a website you already have.</p>
        </div>

        <div class="pricing-grid">
          <article class="pricing-card" data-plan-card tabindex="0" role="group" aria-current="false" aria-label="Website only plan">
            <p class="pricing-tag">Website</p>
            <h3>Website only</h3>
            <p class="pricing-description">A separate, responsive website for your practice.</p>
            <p class="pricing-amount">Contact for pricing</p>
            <p class="pricing-period">Scope and cost confirmed before work begins</p>
            <div class="pricing-divider"></div>
            <ul class="pricing-features">
              <li><i class="fas fa-check"></i><span>Custom website for your practice</span></li>
              <li><i class="fas fa-check"></i><span>Manage website content in your MediCare workspace</span></li>
              <li><i class="fas fa-check"></i><span>Hosting and domain renewals quoted separately</span></li>
            </ul>
            <a class="pricing-action" href="{{ route('care-partner-registration.create', ['plan' => 'website']) }}">Register interest <i class="fas fa-arrow-right"></i></a>
          </article>

          <article class="pricing-card" data-plan-card tabindex="0" role="group" aria-current="false" aria-label="MediCare listing only plan">
            <p class="pricing-tag">MediCare</p>
            <h3>Listing only</h3>
            <p class="pricing-description">Make your practice and care services visible on MediCare.</p>
            <p class="pricing-amount">Contact for pricing</p>
            <p class="pricing-period">Scope and cost confirmed before work begins</p>
            <div class="pricing-divider"></div>
            <ul class="pricing-features">
              <li><i class="fas fa-check"></i><span>MediCare profile for your practice</span></li>
              <li><i class="fas fa-check"></i><span>Showcase services and packages</span></li>
              <li><i class="fas fa-check"></i><span>Manage visible content from your workspace</span></li>
            </ul>
            <a class="pricing-action" href="{{ route('care-partner-registration.create', ['plan' => 'listing']) }}">Register interest <i class="fas fa-arrow-right"></i></a>
          </article>

          <article class="pricing-card selected" data-plan-card tabindex="0" role="group" aria-current="true" aria-label="Website and MediCare listing plan">
            <p class="pricing-tag">Complete presence</p>
            <h3>Website + MediCare listing</h3>
            <p class="pricing-description">Your own practice website and a visible MediCare listing.</p>
            <p class="pricing-amount">Contact for pricing</p>
            <p class="pricing-period">Combined scope and cost confirmed before work begins</p>
            <div class="pricing-divider"></div>
            <ul class="pricing-features">
              <li><i class="fas fa-check"></i><span>Dedicated practice website</span></li>
              <li><i class="fas fa-check"></i><span>MediCare profile, services and packages</span></li>
              <li><i class="fas fa-check"></i><span>One workspace to manage your content</span></li>
            </ul>
            <a class="pricing-action" href="{{ route('care-partner-registration.create', ['plan' => 'website_listing']) }}">Register interest <i class="fas fa-arrow-right"></i></a>
          </article>

          <article class="pricing-card" data-plan-card tabindex="0" role="group" aria-current="false" aria-label="API and backend integration plan">
            <p class="pricing-tag">Already have a website?</p>
            <h3>API &amp; backend integration</h3>
            <p class="pricing-description">Connect a separately built website to your MediCare content.</p>
            <p class="pricing-amount">Contact for pricing</p>
            <p class="pricing-period">Integration scope confirmed after technical review</p>
            <div class="pricing-divider"></div>
            <ul class="pricing-features">
              <li><i class="fas fa-check"></i><span>API token and domain binding setup</span></li>
              <li><i class="fas fa-check"></i><span>Integration guidance for your website team</span></li>
              <li><i class="fas fa-check"></i><span>Ongoing maintenance quoted separately</span></li>
            </ul>
            <a class="pricing-action" href="{{ route('care-partner-registration.create', ['plan' => 'api_integration']) }}">Register interest <i class="fas fa-arrow-right"></i></a>
          </article>
        </div>

        <p class="pricing-note">New care partner accounts are set up by the MediCare team. <a href="{{ route('care-partner-registration.create') }}">Register your interest</a> or <a href="{{ route('login') }}">sign in to an existing account</a>.</p>

        <div class="api-flow">
          <h3>How existing-website API access works</h3>
          <p>Your account gets its own API token. An administrator registers the website domain and enables it in API Domains. Your website team then calls the API from its backend using the token. Keep the token on the server—never expose it in browser JavaScript. Hosting, domain renewal and ongoing third-party website maintenance are not included unless agreed separately.</p>
        </div>
      </div>
    </section>

    <section class="about-section" id="about">
      <div class="section-inner">
        <div class="about-panel">
          <div class="about-visual" aria-hidden="true"><span class="about-cross"><i class="fas fa-plus"></i></span></div>
          <div class="about-copy">
            <p class="eyebrow">For the people who care</p>
            <h2>Make room for better connections.</h2>
            <p>MediCare gives doctors and hospitals a dedicated workspace to manage the information and services they share with their community.</p>
            <a class="button-secondary" href="#plans">Explore plans <i class="fas fa-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </section>

    <section class="contact-section" id="contact">
      <div class="section-inner">
        <div class="section-heading">
          <p class="eyebrow">Get in touch</p>
          <h2>Let’s get your practice connected.</h2>
          <p>Send the MediCare team a message about your practice, an existing account, or our website and API options.</p>
        </div>
        @if (session('contact_submitted'))
          <div class="contact-form-alert" role="status">Thanks for reaching out. Your message has been received by the MediCare team.</div>
        @endif
        @if ($errors->any())
          <div class="contact-form-alert error" role="alert">
            <strong>Please review the following:</strong>
            <ul style="margin:7px 0 0;padding-left:18px">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif
        <div class="contact-grid">
          <article class="contact-card">
            <h3>Care partner support</h3>
            <p>Choose the service that fits your practice, or sign in to manage an existing MediCare workspace.</p>
            <div class="contact-detail"><i class="fas fa-list-alt"></i><div><strong>Explore services</strong><span>Compare website, listing, and API integration options.</span></div></div>
            <div class="contact-detail"><i class="fas fa-user-check"></i><div><strong>Existing care partner</strong><span>Sign in to manage your profile, services, and packages.</span></div></div>
            <div class="contact-detail"><i class="fas fa-user-plus"></i><div><strong>New practice</strong><span>New care partner access is set up by the MediCare team.</span></div></div>
            <div class="contact-quick-links">
              <p>Ready to get started?</p>
              <a class="contact-quick-link" href="{{ route('care-partner-registration.create') }}">Start practice registration <i class="fas fa-arrow-right"></i></a>
              <a class="contact-quick-link secondary" href="{{ route('login') }}">Existing user login <i class="fas fa-sign-in-alt"></i></a>
            </div>
          </article>
          <article class="contact-form-card">
            <p class="eyebrow">Contact MediCare</p>
            <h3>How can we help?</h3>
            <form class="contact-form" method="POST" action="{{ route('contact.store') }}">
              @csrf
              <div class="contact-form-grid">
                <div class="contact-form-field">
                  <label for="contact-name">Your name</label>
                  <input id="contact-name" name="name" value="{{ old('name') }}" maxlength="120" autocomplete="name" required>
                </div>
                <div class="contact-form-field">
                  <label for="contact-email">Email address</label>
                  <input id="contact-email" name="email" type="email" value="{{ old('email') }}" maxlength="191" autocomplete="email" required>
                </div>
                <div class="contact-form-field">
                  <label for="contact-phone">Phone <span style="font-weight:400">(optional)</span></label>
                  <input id="contact-phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="30" autocomplete="tel">
                </div>
                <div class="contact-form-field">
                  <label for="contact-subject">Subject</label>
                  <input id="contact-subject" name="subject" value="{{ old('subject') }}" maxlength="160" required>
                </div>
                <div class="contact-form-field wide">
                  <label for="contact-message">Message</label>
                  <textarea id="contact-message" name="message" maxlength="5000" required>{{ old('message') }}</textarea>
                </div>
              </div>
              <button class="contact-form-submit" type="submit"><i class="fas fa-paper-plane"></i> Send message</button>
              <p class="contact-form-note">Your message will be saved for the MediCare team. New enquiries are reviewed in the admin Contact messages inbox.</p>
            </form>
          </article>
        </div>
      </div>
    </section>

    <section class="branch-section" aria-labelledby="branches-title">
      <div class="section-inner">
        <div class="section-heading">
          <p class="eyebrow">Branch locations</p>
          <h2 id="branches-title">MediCare branches.</h2>
          <p>Sample locations are shown below as a layout preview.</p>
        </div>
        <div class="branch-grid">
          <article class="branch-card">
            <div class="branch-card-heading">
              <span class="branch-icon"><i class="fas fa-map-marker-alt"></i></span>
              <div><h3>Sample Branch One</h3><span class="branch-demo-label">Demo address only</span></div>
            </div>
            <p class="branch-address">123 Example Road, Demo Building<br>Sample City, Sample State — 000001</p>
          </article>
          <article class="branch-card">
            <div class="branch-card-heading">
              <span class="branch-icon"><i class="fas fa-map-marker-alt"></i></span>
              <div><h3>Sample Branch Two</h3><span class="branch-demo-label">Demo address only</span></div>
            </div>
            <p class="branch-address">45 Placeholder Avenue, Demo Centre<br>Example City, Example State — 000002</p>
          </article>
        </div>
        <p class="branch-disclaimer">These are fictional sample addresses for display purposes only; they are not MediCare office locations.</p>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="footer-inner">
      <p>© {{ date('Y') }} MediCare. Thoughtful care, one connection at a time.</p>
      <div class="footer-links"><a href="#home">Home</a><a href="#services">Services</a><a href="#directory">Listings</a><a href="#plans">Plans</a><a href="#contact">Contact</a><a href="{{ route('login') }}">Existing user login</a></div>
    </div>
  </footer>
  <script>
    const planCards = document.querySelectorAll('[data-plan-card]');

    function selectPlan(selectedCard) {
      planCards.forEach((card) => {
        const isSelected = card === selectedCard;
        card.classList.toggle('selected', isSelected);
        card.setAttribute('aria-current', String(isSelected));
      });
    }

    planCards.forEach((card) => {
      card.addEventListener('click', () => selectPlan(card));

      card.addEventListener('keydown', (event) => {
        if (event.target !== card) {
          return;
        }

        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          selectPlan(card);
        }
      });
    });
  </script>
</body>
</html>
