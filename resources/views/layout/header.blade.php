<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#f7f6f1">
  <title>@yield('title', 'Overview') | MediCare</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root {
      color-scheme: light;
      --ink: #26382f;
      --muted: #79847b;
      --line: #e9e8df;
      --paper: #f7f6f1;
      --white: #fffefa;
      --green: #54745d;
      --green-dark: #304b3b;
      --sage: #e8eee5;
      --clay: #bd795e;
      --gold: #c39a58;
      --shadow: 0 12px 36px rgba(46, 60, 48, .055);
    }
    * { box-sizing: border-box; }
    body { margin: 0; background: var(--paper); color: var(--ink); font-family: 'DM Sans', sans-serif; -webkit-font-smoothing: antialiased; }
    button, input { font: inherit; }
    a { color: inherit; text-decoration: none; }
    html, body { height: 100%; overflow: hidden; }
    .app-shell { height: 100vh; display: flex; }
    @supports (height: 100dvh) { .app-shell { height: 100dvh; } }
    .sidebar { position: fixed; z-index: 10; top: 0; bottom: 0; left: 0; width: 250px; display: flex; flex-direction: column; padding: 0 18px; overflow: hidden; background: #fff; border-right: 1px solid var(--line); }
    .brand { min-height: 78px; display: flex; flex: 0 0 78px; align-items: center; gap: 11px; padding: 0 28px; margin: 0 -18px; background: #fff; border-bottom: 1px solid var(--line); }
    .brand-mark { width: 39px; height: 39px; display: grid; place-items: center; color: #fff; background: var(--green); border-radius: 14px 14px 14px 5px; font-size: 16px; box-shadow: 0 6px 15px rgba(84,116,93,.2); }
    .brand-name { font-family: 'Playfair Display', serif; font-size: 21px; font-weight: 600; letter-spacing: -.5px; }
    .brand-name span { color: var(--green); }
    .sidebar-scroll { min-height: 0; flex: 1; display: flex; flex-direction: column; overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; padding: 30px 0 20px; }
    .workspace-label { display: flex; align-items: center; gap: 9px; padding: 0 11px; margin-bottom: 30px; color: var(--muted); font-size: 11px; font-weight: 700; letter-spacing: 1.1px; text-transform: uppercase; }
    .workspace-dot { width: 8px; height: 8px; background: #88a58a; border-radius: 50%; box-shadow: 0 0 0 4px #edf2ea; }
    .nav-label { padding: 0 11px; margin: 0 0 11px; color: #a0a69e; font-size: 10px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; }
    .sidebar-nav { display: grid; gap: 5px; }
    .sidebar-link { min-height: 43px; display: flex; align-items: center; gap: 13px; padding: 0 12px; color: #717c73; border-radius: 9px; font-size: 13px; font-weight: 500; transition: background .18s ease, color .18s ease; }
    .sidebar-link i { width: 17px; text-align: center; font-size: 14px; }
    .sidebar-link:hover { color: var(--green-dark); background: #f4f6f1; }
    .sidebar-link.active { color: var(--green-dark); background: var(--sage); font-weight: 700; }
    .sidebar-toggle { width: 100%; text-align: left; background: transparent; border: 0; cursor: pointer; }
    .submenu-chevron { width: auto !important; margin-left: auto; font-size: 10px !important; transition: transform .18s ease; }
    .sidebar-toggle[aria-expanded="true"] .submenu-chevron { transform: rotate(180deg); }
    .sidebar-submenu { display: grid; gap: 3px; padding: 5px 0 4px 30px; }
    .sidebar-submenu[hidden] { display: none; }
    .sidebar-submenu-link { min-height: 36px; display: flex; align-items: center; gap: 11px; padding: 0 12px; color: #7b867c; border-radius: 8px; font-size: 12px; transition: background .18s ease, color .18s ease; }
    .sidebar-submenu-link:hover { color: var(--green-dark); background: #f4f6f1; }
    .sidebar-submenu-link.active { color: var(--green-dark); background: #f1f4ee; font-weight: 700; }
    .submenu-dot { width: 5px; height: 5px; background: #9aa99a; border-radius: 50%; }
    .nav-count { margin-left: auto; padding: 3px 7px; color: #9b674f; background: #f7ebe4; border-radius: 20px; font-size: 10px; font-weight: 700; }
    .sidebar-spacer { flex: 1; min-height: 36px; }
    .welcome-note { padding: 16px 15px; background: #f4f2e9; border: 1px solid #eeece2; border-radius: 12px; }
    .welcome-note i { color: var(--gold); font-size: 14px; }
    .welcome-note p { margin: 10px 0 0; color: #69776a; font-family: 'Playfair Display', serif; font-size: 14px; line-height: 1.55; }
    .sidebar-bottom { margin-top: 15px; padding-top: 13px; border-top: 1px solid var(--line); }
    .sidebar-link.logout { color: #8f7770; }
    .main-panel { min-width: 0; height: 100%; display: flex; flex: 1; flex-direction: column; margin-left: 250px; overflow: hidden; }
    .topbar { min-height: 78px; display: flex; flex: 0 0 78px; align-items: center; justify-content: space-between; gap: 20px; padding: 0 42px; background: #fff; border-bottom: 1px solid var(--line); }
    .topbar-left, .topbar-actions, .admin-profile { display: flex; align-items: center; }
    .topbar-left { gap: 14px; }
    .breadcrumb { color: #92998f; font-size: 12px; }
    .breadcrumb strong { color: #526255; font-weight: 600; }
    .topbar-actions { gap: 22px; }
    .icon-button { position: relative; width: 36px; height: 36px; display: grid; place-items: center; color: #727d73; background: transparent; border: 0; border-radius: 50%; cursor: pointer; }
    .icon-button:hover { background: var(--sage); }
    .notification-dot { position: absolute; top: 7px; right: 7px; width: 7px; height: 7px; background: var(--clay); border: 1.5px solid var(--paper); border-radius: 50%; }
    .admin-profile { gap: 10px; padding-left: 19px; border-left: 1px solid var(--line); }
    .avatar { width: 37px; height: 37px; display: grid; place-items: center; color: var(--green-dark); background: #e7eade; border-radius: 50%; font-family: 'Playfair Display', serif; font-size: 14px; font-weight: 700; }
    .profile-copy { display: grid; gap: 2px; }
    .profile-copy strong { font-size: 12px; font-weight: 700; }
    .profile-copy span { color: #90988f; font-size: 10px; }
    .mobile-menu { display: none; }
    .content-scroll { min-height: 0; flex: 1; overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin; scrollbar-color: #c7d1c5 transparent; padding: 0 42px 35px; }
    .content-wrap { max-width: 1420px; margin: 0 auto; padding-top: 30px; }
    .page-heading { display: flex; align-items: end; justify-content: space-between; gap: 20px; margin-bottom: 23px; }
    .eyebrow { display: flex; align-items: center; gap: 8px; margin: 0 0 8px; color: var(--green); font-size: 10px; font-weight: 700; letter-spacing: 1.25px; text-transform: uppercase; }
    .eyebrow:before { width: 17px; height: 1px; background: var(--green); content: ''; }
    h1, h2, h3, p { margin-top: 0; }
    h1 { margin-bottom: 5px; font-family: 'Playfair Display', serif; font-size: clamp(27px, 3vw, 34px); font-weight: 500; letter-spacing: -.7px; }
    .page-subtitle { margin: 0; color: var(--muted); font-size: 12px; }
    .date-chip { display: inline-flex; align-items: center; gap: 9px; padding: 10px 13px; color: #637066; background: var(--white); border: 1px solid var(--line); border-radius: 8px; font-size: 11px; white-space: nowrap; }
    .date-chip i { color: var(--green); }
    .welcome-banner { position: relative; min-height: 182px; display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 29px 35px; overflow: hidden; background: #e7ede3; border: 1px solid #dfe7dc; border-radius: 14px; }
    .welcome-banner:before, .welcome-banner:after { position: absolute; border: 1px solid rgba(83,114,91,.12); border-radius: 50%; content: ''; pointer-events: none; }
    .welcome-banner:before { width: 270px; height: 270px; top: -143px; right: 117px; }
    .welcome-banner:after { width: 350px; height: 350px; top: -177px; right: 77px; }
    .welcome-copy { position: relative; z-index: 1; max-width: 590px; }
    .welcome-copy .eyebrow { color: #668069; margin-bottom: 9px; }
    .welcome-copy h2 { margin-bottom: 8px; color: #304b3b; font-family: 'Playfair Display', serif; font-size: clamp(23px, 2.5vw, 30px); font-weight: 500; letter-spacing: -.4px; }
    .welcome-copy p { max-width: 440px; margin: 0; color: #718075; font-size: 12px; line-height: 1.7; }
    .banner-action { position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 10px; padding: 11px 15px; color: #fff; background: var(--green-dark); border-radius: 7px; font-size: 11px; font-weight: 600; transition: background .18s ease, transform .18s ease; white-space: nowrap; }
    .banner-action:hover { background: #253e30; transform: translateY(-1px); }
    .banner-art { position: absolute; width: 155px; height: 155px; right: 225px; bottom: -45px; border-radius: 50%; background: rgba(255,254,250,.32); }
    .banner-art:before { position: absolute; width: 82px; height: 82px; top: 18px; left: 31px; border: 1px solid rgba(84,116,93,.22); border-radius: 50%; content: ''; }
    .banner-art:after { position: absolute; width: 12px; height: 12px; top: 29px; left: 115px; background: #c39a58; border-radius: 50%; box-shadow: -86px 78px 0 -3px #bd795e; content: ''; }
    .stats-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin: 20px 0; }
    .stat-card { min-height: 116px; padding: 16px 18px; background: var(--white); border: 1px solid var(--line); border-radius: 11px; box-shadow: 0 4px 14px rgba(46,60,48,.025); }
    .stat-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; }
    .stat-label { color: #7a847b; font-size: 11px; font-weight: 500; }
    .stat-icon { width: 30px; height: 30px; display: grid; place-items: center; color: var(--green); background: #edf2ea; border-radius: 9px; font-size: 12px; }
    .stat-icon.clay { color: #aa6d52; background: #f7ece5; }
    .stat-icon.gold { color: #aa8548; background: #f6f0e2; }
    .stat-icon.blue { color: #637e83; background: #eaf0ef; }
    .stat-value { display: flex; align-items: baseline; gap: 8px; }
    .stat-value strong { font-family: 'Playfair Display', serif; font-size: 27px; font-weight: 600; line-height: 1; }
    .stat-trend { color: #7b987d; font-size: 10px; }
    .stat-foot { margin-top: 7px; color: #a0a69e; font-size: 10px; }
    .dashboard-grid { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(260px, .85fr); gap: 16px; align-items: start; }
    .panel { background: var(--white); border: 1px solid var(--line); border-radius: 11px; box-shadow: 0 4px 14px rgba(46,60,48,.025); }
    .panel-heading { display: flex; align-items: center; justify-content: space-between; gap: 15px; padding: 18px 20px 15px; }
    .panel-title { margin: 0 0 4px; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 600; }
    .panel-caption { margin: 0; color: #9aa198; font-size: 10px; }
    .text-link { display: inline-flex; align-items: center; gap: 7px; color: var(--green); font-size: 10px; font-weight: 700; }
    .text-link:hover { color: var(--green-dark); }
    .schedule-list { padding: 0 20px 6px; }
    .schedule-row { min-height: 65px; display: grid; grid-template-columns: 56px minmax(0, 1fr) auto; align-items: center; gap: 12px; border-top: 1px solid #f0efe9; }
    .schedule-time { color: #68776a; font-size: 11px; font-weight: 700; }
    .patient-info { min-width: 0; display: flex; align-items: center; gap: 10px; }
    .patient-avatar { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: #657563; background: #eff1e9; border-radius: 50%; font-family: 'Playfair Display', serif; font-size: 11px; font-weight: 700; }
    .patient-avatar.peach { color: #9a6b54; background: #f5eae2; }
    .patient-avatar.blue { color: #59737a; background: #e8eff0; }
    .patient-avatar.yellow { color: #92774c; background: #f4efdf; }
    .patient-copy { min-width: 0; display: grid; gap: 4px; }
    .patient-copy strong { overflow: hidden; color: #3c4b40; font-size: 11px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
    .patient-copy span { overflow: hidden; color: #9aa198; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
    .status { padding: 5px 8px; border-radius: 20px; font-size: 9px; font-weight: 700; white-space: nowrap; }
    .status.checked { color: #57765b; background: #edf3eb; }
    .status.waiting { color: #9d7441; background: #f8f1e3; }
    .status.upcoming { color: #7f8580; background: #f0f1ed; }
    .guest-card { padding-bottom: 18px; }
    .guest-intro { padding: 0 19px; color: #899188; font-size: 11px; line-height: 1.65; }
    .guest-score { display: flex; align-items: center; gap: 14px; padding: 13px 19px 17px; }
    .score-ring { width: 68px; height: 68px; flex: 0 0 68px; display: grid; place-items: center; background: conic-gradient(var(--green) 0deg 302deg, #e8ece4 302deg 360deg); border-radius: 50%; position: relative; }
    .score-ring:before { position: absolute; inset: 6px; background: var(--white); border-radius: 50%; content: ''; }
    .score-ring strong { z-index: 1; color: var(--green-dark); font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 600; }
    .score-copy strong { display: block; margin-bottom: 5px; font-size: 12px; }
    .score-copy span { color: #9aa198; font-size: 10px; line-height: 1.5; }
    .guest-divider { margin: 0 19px; border-top: 1px solid #f0efe9; }
    .guest-foot { display: flex; justify-content: space-between; padding: 14px 19px 0; color: #899188; font-size: 10px; }
    .guest-foot strong { color: var(--green); font-weight: 700; }
    .services-panel { margin-top: 16px; }
    .service-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; padding: 0 20px 20px; }
    .service-item { display: flex; align-items: center; gap: 11px; min-height: 64px; padding: 10px 12px; background: #faf9f5; border: 1px solid #f0efe8; border-radius: 8px; }
    .service-icon { width: 32px; height: 32px; flex: 0 0 32px; display: grid; place-items: center; color: var(--green); background: #eaf0e7; border-radius: 9px; font-size: 12px; }
    .service-icon.clay { color: #a66d54; background: #f6ebe4; }
    .service-icon.gold { color: #a5854d; background: #f5efdf; }
    .service-copy strong { display: block; margin-bottom: 4px; font-size: 10px; font-weight: 700; }
    .service-copy span { color: #969e95; font-size: 9px; }
    .footer-note { padding: 22px 0 3px; color: #a1a69e; font-size: 10px; text-align: center; }
    .footer-note i { margin-right: 5px; color: #87a087; }
    .category-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 22px; }
    .category-toolbar h1 { margin-bottom: 5px; }
    .category-add-button { display: inline-flex; align-items: center; justify-content: center; gap: 9px; min-height: 41px; padding: 0 15px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font-size: 12px; font-weight: 600; transition: background .18s ease, transform .18s ease; }
    .category-add-button:hover { background: #253e30; transform: translateY(-1px); }
    .category-table-wrap { overflow-x: auto; }
    .sr-only { position: absolute; width: 1px; height: 1px; padding: 0; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
    .service-filter { display: flex; align-items: center; justify-content: flex-end; gap: 8px; }
    .service-filter-select { width: 185px; min-height: 36px; padding: 8px 30px 8px 10px; font-size: 11px; }
    .category-filter-button, .category-reset-button { min-height: 36px; display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 0 11px; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 600; white-space: nowrap; }
    .category-filter-button { color: #fff; background: var(--green-dark); border: 1px solid var(--green-dark); }
    .category-filter-button:hover { background: #253e30; }
    .category-reset-button { color: #6e796f; background: #fff; border: 1px solid #e5e6de; }
    .category-reset-button:hover { color: var(--green-dark); background: #f4f6f1; }
    .category-table { width: 100%; border-collapse: collapse; text-align: left; }
    .category-table th { padding: 12px 18px; color: #92998f; background: #faf9f5; border-bottom: 1px solid var(--line); font-size: 10px; font-weight: 700; letter-spacing: .6px; text-transform: uppercase; white-space: nowrap; }
    .category-table td { padding: 13px 18px; color: #566257; border-bottom: 1px solid #f0efe9; font-size: 12px; }
    .category-table tr:last-child td { border-bottom: 0; }
    .category-name-cell { display: flex; align-items: center; gap: 12px; font-weight: 600; }
    .category-visual { width: 38px; height: 38px; flex: 0 0 38px; display: grid; place-items: center; overflow: hidden; color: var(--green); background: #edf2ea; border: 1px solid #e7ece4; border-radius: 9px; font-size: 16px; }
    .category-visual img { width: 100%; height: 100%; object-fit: cover; }
    .category-visual svg { width: 22px; height: 22px; }
    .category-type { display: inline-flex; align-items: center; padding: 5px 9px; color: #6d796e; background: #f2f3ee; border-radius: 20px; font-size: 10px; font-weight: 600; text-transform: capitalize; }
    .category-status { display: inline-flex; align-items: center; padding: 5px 9px; border-radius: 20px; font-size: 10px; font-weight: 700; }
    .category-status.active { color: #57765b; background: #edf3eb; }
    .category-status.inactive { color: #9a6855; background: #f7ece5; }
    .category-actions { display: flex; align-items: center; gap: 7px; }
    .category-actions form { margin: 0; }
    .category-icon-button { width: 32px; height: 32px; display: grid; place-items: center; color: #6f806f; background: #f4f5f0; border: 1px solid transparent; border-radius: 7px; cursor: pointer; transition: background .18s ease, color .18s ease; }
    .category-icon-button:hover { color: var(--green-dark); background: #eaf0e7; }
    .category-icon-button.deactivate { color: #a36d58; }
    .category-icon-button.activate { color: #5c805f; }
    .category-icon-button.deactivate:hover { background: #f8ece6; }
    .category-icon-button.activate:hover { background: #eaf2e8; }
    .category-date { color: #8e978e !important; white-space: nowrap; }
    .category-empty { padding: 48px 20px !important; color: #92998f !important; text-align: center; }
    .category-empty i { display: block; margin-bottom: 12px; color: #9eaf9e; font-size: 23px; }
    .category-alert { margin-bottom: 16px; padding: 12px 15px; color: #496a4d; background: #edf3eb; border: 1px solid #dfeadd; border-radius: 8px; font-size: 12px; }
    .category-alert.error { color: #945f50; background: #fbefea; border-color: #f2e0d8; }
    .category-pagination { display: flex; justify-content: flex-end; padding: 15px 18px; }
    .category-pagination nav { width: 100%; display: flex; align-items: center; justify-content: flex-end; }
    .category-pagination nav { width: 100%; }
    .category-pagination nav .relative.z-0.inline-flex { display: inline-flex; align-items: center; overflow: hidden; border-radius: 8px; box-shadow: none; }
    .category-pagination nav .relative.z-0.inline-flex > a,
    .category-pagination nav .relative.z-0.inline-flex > span { min-width: 34px; height: 34px; display: inline-flex; align-items: center; justify-content: center; margin: 0 0 0 -1px; padding: 0 9px; color: #748075; background: #fff; border: 1px solid var(--line); font-size: 11px; line-height: 1; text-align: center; text-decoration: none; }
    .category-pagination nav .relative.z-0.inline-flex > span > span { width: 100%; height: 100%; display: inline-flex; align-items: center; justify-content: center; padding: 0 9px; }
    .category-pagination nav .relative.z-0.inline-flex > span:first-child,
    .category-pagination nav .relative.z-0.inline-flex > a:first-child { margin-left: 0; }
    .category-pagination nav .relative.z-0.inline-flex > a:hover { z-index: 1; color: var(--green-dark); background: var(--sage); }
    .category-pagination nav .relative.z-0.inline-flex > span[aria-current="page"] { color: var(--green-dark); background: var(--sage); font-weight: 700; }
    .category-pagination nav .relative.z-0.inline-flex > span[aria-disabled="true"] > span { color: #b5bcb4; background: #fafaf7; }
    .category-pagination nav .relative.z-0.inline-flex svg { width: 16px; height: 16px; display: block; }
    .category-pagination nav .relative.z-0.inline-flex > a:focus-visible { z-index: 2; outline: 2px solid #8aa18b; outline-offset: -2px; }
    .category-modal[hidden] { display: none; }
    .category-modal { position: fixed; z-index: 30; inset: 0; display: grid; place-items: center; overflow-y: auto; padding: 20px; background: rgba(38, 56, 47, .42); }
    .category-dialog { width: min(100%, 540px); max-height: calc(100vh - 40px); overflow-y: auto; background: #fffefa; border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 22px 65px rgba(29, 45, 34, .22); }
    .category-dialog-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 15px; padding: 22px 24px 17px; border-bottom: 1px solid var(--line); }
    .category-dialog-header h2 { margin: 0 0 5px; font-family: 'Playfair Display', serif; font-size: 21px; font-weight: 600; }
    .category-dialog-header p { margin: 0; color: #92998f; font-size: 11px; }
    .category-modal-close { width: 32px; height: 32px; display: grid; place-items: center; color: #7b867c; background: #f4f4ef; border: 0; border-radius: 50%; cursor: pointer; }
    .category-form { padding: 20px 24px 23px; }
    .category-field { margin-bottom: 19px; }
    .category-options-fieldset { padding: 0; border: 0; margin-right: 0; margin-left: 0; }
    .category-field > label, .category-field-label { display: block; margin-bottom: 8px; color: #526255; font-size: 11px; font-weight: 700; }
    .category-input, .category-textarea { width: 100%; padding: 11px 12px; color: var(--ink); background: #fff; border: 1px solid #e5e6de; border-radius: 7px; outline: none; font-size: 12px; transition: border .18s ease, box-shadow .18s ease; }
    .category-input:focus, .category-textarea:focus { border-color: #8aa18b; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
    .category-textarea { min-height: 120px; resize: vertical; font-family: Consolas, monospace; font-size: 11px; line-height: 1.5; }
    .category-help { margin: 7px 0 0; color: #9aa198; font-size: 10px; line-height: 1.5; }
    .visual-options { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
    .visual-option { min-width: 0; cursor: pointer; }
    .visual-option input { position: absolute; width: 1px; height: 1px; overflow: hidden; opacity: 0; }
    .visual-option-card { min-height: 70px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 7px; color: #788278; background: #fff; border: 1px solid #e7e8e0; border-radius: 8px; font-size: 10px; transition: background .18s ease, border .18s ease, color .18s ease; }
    .visual-option-card i { font-size: 15px; }
    .visual-option input:checked + .visual-option-card { color: var(--green-dark); background: #f0f4ee; border-color: #8fa48f; }
    .visual-option input:focus-visible + .visual-option-card { outline: 3px solid rgba(84,116,93,.2); outline-offset: 2px; }
    .visual-panel { margin-top: 13px; }
    .visual-panel[hidden] { display: none; }
    .file-input { width: 100%; padding: 9px; background: #faf9f5; border: 1px dashed #d9ddd4; border-radius: 7px; color: #7a847b; font-size: 11px; }
    .category-error { margin: 6px 0 0; color: #a45f4e; font-size: 10px; }
    .category-form-actions { display: flex; justify-content: flex-end; gap: 9px; margin-top: 24px; }
    .category-cancel { min-height: 39px; padding: 0 14px; color: #6e796f; background: transparent; border: 1px solid #e5e6de; border-radius: 7px; cursor: pointer; font-size: 11px; font-weight: 600; }
    .category-save { min-height: 39px; padding: 0 17px; color: #fff; background: var(--green-dark); border: 0; border-radius: 7px; cursor: pointer; font-size: 11px; font-weight: 600; }
    .category-save:hover { background: #253e30; }
    .doctor-dialog { width: min(100%, 680px); }
    .doctor-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 14px; }
    .doctor-form-grid .category-field { min-width: 0; }
    .doctor-field-wide { grid-column: 1 / -1; }
    .doctor-short-textarea { min-height: 76px; font-family: 'DM Sans', sans-serif; }
    .doctor-optional { color: #9aa198; font-size: 10px; font-weight: 400; }
    .doctor-name-cell { display: flex; align-items: center; gap: 10px; min-width: 185px; }
    .doctor-avatar { width: 35px; height: 35px; flex: 0 0 35px; display: grid; place-items: center; color: var(--green-dark); background: var(--sage); border-radius: 50%; font-family: 'Playfair Display', serif; font-size: 13px; font-weight: 700; }
    .doctor-name-copy { min-width: 0; display: grid; gap: 4px; }
    .doctor-name-copy strong { overflow: hidden; color: #3c4b40; font-size: 11px; font-weight: 600; text-overflow: ellipsis; white-space: nowrap; }
    .doctor-name-copy span { overflow: hidden; color: #9aa198; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
    .doctor-website { display: inline-flex; align-items: center; gap: 6px; color: var(--green); font-size: 11px; font-weight: 600; }
    .doctor-website:hover { color: var(--green-dark); }
    .doctor-actions { min-width: 190px; }
    .doctor-login-button { min-height: 31px; display: inline-flex; align-items: center; gap: 6px; padding: 0 9px; color: var(--green-dark); background: #edf2ea; border: 1px solid #e2e9df; border-radius: 7px; cursor: pointer; font-size: 10px; font-weight: 600; white-space: nowrap; }
    .doctor-login-button:hover { background: #e2ebdf; }
    .doctor-login-button:disabled { cursor: wait; opacity: .65; }
    .doctor-access-created { color: #7b867c; font-size: 9px; white-space: nowrap; }
    .doctor-credentials-content { padding: 20px 24px 23px; }
    .doctor-credential-row { display: grid; grid-template-columns: 82px minmax(0, 1fr) 32px; align-items: center; gap: 10px; min-height: 52px; padding: 8px 0; border-bottom: 1px solid #f0efe9; }
    .doctor-credential-row > span { color: #899188; font-size: 11px; }
    .doctor-credential-row > strong { overflow-wrap: anywhere; color: #36483a; font-family: Consolas, monospace; font-size: 12px; }
    .sidebar-scroll::-webkit-scrollbar, .content-scroll::-webkit-scrollbar { width: 6px; }
    .sidebar-scroll::-webkit-scrollbar-thumb, .content-scroll::-webkit-scrollbar-thumb { background: #c7d1c5; border: 2px solid transparent; border-radius: 8px; background-clip: padding-box; }
    .sidebar-scroll::-webkit-scrollbar-thumb:hover, .content-scroll::-webkit-scrollbar-thumb:hover { background-color: #9eaf9e; }
    .sidebar-backdrop { display: none; }
    @media (max-width: 1050px) {
      .sidebar { width: 220px; }
      .main-panel { margin-left: 220px; }
      .topbar { padding-right: 25px; padding-left: 25px; }
      .content-scroll { padding-right: 25px; padding-left: 25px; }
      .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
      .banner-art { right: 150px; }
    }
    @media (max-width: 760px) {
      .sidebar { position: fixed; z-index: 10; top: 0; bottom: 0; left: 0; width: 255px; transform: translateX(-100%); transition: transform .22s ease; box-shadow: 12px 0 35px rgba(34,48,38,.12); }
      .app-shell.sidebar-open .sidebar { transform: translateX(0); }
      .sidebar-backdrop { position: fixed; z-index: 9; inset: 0; display: block; visibility: hidden; background: rgba(37,51,41,.25); opacity: 0; transition: opacity .2s ease, visibility .2s ease; }
      .app-shell.sidebar-open .sidebar-backdrop { visibility: visible; opacity: 1; }
      .main-panel { margin-left: 0; }
      .topbar { min-height: 66px; flex-basis: 66px; padding-right: 18px; padding-left: 18px; }
      .brand { min-height: 66px; flex-basis: 66px; }
      .mobile-menu { display: grid; }
      .topbar-actions { gap: 9px; }
      .admin-profile { padding-left: 10px; }
      .profile-copy { display: none; }
      .content-scroll { padding: 0 18px 25px; }
      .content-wrap { padding-top: 24px; }
      .welcome-banner { min-height: 195px; align-items: flex-start; flex-direction: column; justify-content: center; padding: 25px 23px; }
      .banner-action { margin-top: 1px; }
      .banner-art { right: 25px; bottom: -61px; }
      .dashboard-grid { grid-template-columns: 1fr; }
      .service-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 480px) {
      .topbar { padding-right: 13px; padding-left: 13px; }
      .content-scroll { padding-right: 13px; padding-left: 13px; }
      .page-heading { align-items: flex-start; flex-direction: column; gap: 12px; }
      .date-chip { padding: 8px 10px; }
      .stats-grid { gap: 9px; margin: 14px 0; }
      .stat-card { min-height: 105px; padding: 13px; }
      .stat-top { margin-bottom: 12px; }
      .stat-value strong { font-size: 24px; }
      .schedule-list { padding-right: 13px; padding-left: 13px; }
      .panel-heading { padding-right: 14px; padding-left: 14px; }
      .schedule-row { grid-template-columns: 47px minmax(0, 1fr) auto; gap: 8px; }
      .patient-avatar { width: 29px; height: 29px; flex-basis: 29px; }
      .status { padding: 4px 6px; font-size: 8px; }
      .service-grid { grid-template-columns: 1fr; padding-right: 14px; padding-left: 14px; }
      .welcome-banner { padding-right: 18px; padding-left: 18px; }
      .welcome-copy p { max-width: 290px; }
      .category-toolbar { align-items: flex-start; flex-direction: column; }
      .category-toolbar .category-add-button { width: 100%; }
      .service-list-heading { align-items: stretch; flex-direction: column; }
      .service-filter { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; }
      .service-filter-select { width: 100%; }
      .category-dialog-header, .category-form { padding-right: 17px; padding-left: 17px; }
      .visual-options { gap: 5px; }
      .visual-option-card { min-height: 63px; font-size: 9px; }
      .doctor-form-grid { grid-template-columns: 1fr; }
      .doctor-field-wide { grid-column: auto; }
      .doctor-credentials-content { padding-right: 17px; padding-left: 17px; }
    }
    @media (prefers-reduced-motion: reduce) {
      *, *:before, *:after { scroll-behavior: auto !important; transition-duration: .01ms !important; animation-duration: .01ms !important; }
    }
  </style>
</head>
<body>
