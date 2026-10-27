<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#f7f6f1">
  <title>Sign in | MediCare</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    :root { color-scheme: light; --ink: #26382f; --muted: #79847b; --line: #e9e8df; --paper: #f7f6f1; --green: #54745d; --green-dark: #304b3b; --sage: #e8eee5; }
    * { box-sizing: border-box; }
    body { min-height: 100vh; display: grid; place-items: center; margin: 0; padding: 24px; color: var(--ink); background: radial-gradient(circle at 12% 12%, #e7ede3 0, transparent 31%), radial-gradient(circle at 92% 85%, #f0e9dd 0, transparent 30%), var(--paper); font-family: 'DM Sans', sans-serif; }
    .login-shell { width: min(100%, 960px); min-height: 560px; display: grid; grid-template-columns: 1fr 1fr; overflow: hidden; background: #fffefa; border: 1px solid var(--line); border-radius: 22px; box-shadow: 0 28px 80px rgba(46, 60, 48, .12); }
    .welcome-panel { position: relative; display: flex; flex-direction: column; justify-content: space-between; padding: 42px; overflow: hidden; color: #fff; background: linear-gradient(145deg, rgba(39, 67, 49, .95), rgba(84, 116, 93, .94)); }
    .welcome-panel:after { position: absolute; right: -85px; bottom: -135px; width: 340px; height: 340px; border: 1px solid rgba(255,255,255,.18); border-radius: 50%; box-shadow: 0 0 0 32px rgba(255,255,255,.035), 0 0 0 66px rgba(255,255,255,.025); content: ''; }
    .brand { position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 11px; color: #fff; font-family: 'Playfair Display', serif; font-size: 21px; font-weight: 600; }
    .brand-mark { width: 40px; height: 40px; display: grid; place-items: center; color: var(--green-dark); background: #fffefa; border-radius: 14px 14px 14px 5px; }
    .welcome-copy { position: relative; z-index: 1; max-width: 350px; margin: auto 0; padding: 40px 0; }
    .eyebrow { margin-bottom: 14px; color: #dbe8d8; font-size: 10px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
    h1 { margin: 0 0 14px; font-family: 'Playfair Display', serif; font-size: clamp(31px, 4vw, 43px); font-weight: 500; line-height: 1.12; }
    .welcome-copy p { margin: 0; color: rgba(255,255,255,.78); font-size: 13px; line-height: 1.8; }
    .trust-note { position: relative; z-index: 1; display: flex; align-items: center; gap: 9px; color: rgba(255,255,255,.75); font-size: 11px; }
    .trust-note i { color: #d9c28f; }
    .form-panel { display: flex; flex-direction: column; justify-content: center; padding: 55px clamp(28px, 6vw, 64px); }
    .form-heading { margin-bottom: 28px; }
    .form-heading h2 { margin: 0 0 8px; font-family: 'Playfair Display', serif; font-size: 27px; font-weight: 600; }
    .form-heading p { margin: 0; color: var(--muted); font-size: 12px; line-height: 1.6; }
    .field { margin-bottom: 18px; }
    label { display: block; margin-bottom: 8px; color: #526255; font-size: 11px; font-weight: 700; }
    .input-wrap { position: relative; }
    .input-wrap i { position: absolute; top: 50%; left: 13px; color: #99a297; font-size: 13px; transform: translateY(-50%); }
    input[type="text"], input[type="password"] { width: 100%; min-height: 45px; padding: 0 13px 0 38px; color: var(--ink); background: #fff; border: 1px solid #e5e6de; border-radius: 8px; outline: none; font: inherit; font-size: 12px; transition: border .18s ease, box-shadow .18s ease; }
    input:focus { border-color: #8aa18b; box-shadow: 0 0 0 3px rgba(84,116,93,.1); }
    .form-options { display: flex; align-items: center; justify-content: space-between; margin: 4px 0 22px; }
    .remember { display: inline-flex; align-items: center; gap: 8px; margin: 0; color: var(--muted); font-size: 11px; font-weight: 500; }
    .remember input { accent-color: var(--green); }
    .submit-btn { width: 100%; min-height: 45px; color: #fff; background: var(--green-dark); border: 0; border-radius: 8px; cursor: pointer; font: inherit; font-size: 12px; font-weight: 700; transition: background .18s ease, transform .18s ease; }
    .submit-btn:hover { background: #253e30; transform: translateY(-1px); }
    .alert-box { margin-bottom: 18px; padding: 11px 13px; color: #945f50; background: #fbefea; border: 1px solid #f2e0d8; border-radius: 8px; font-size: 11px; }
    .help-note { margin: 24px 0 0; color: #9aa198; font-size: 10px; line-height: 1.6; text-align: center; }
    @media (max-width: 700px) { body { padding: 12px; } .login-shell { max-width: 460px; min-height: 0; grid-template-columns: 1fr; } .welcome-panel { min-height: 210px; padding: 25px; } .welcome-copy { padding: 30px 0 18px; } .welcome-copy h1 { font-size: 30px; } .welcome-copy p { font-size: 12px; } .trust-note { display: none; } .form-panel { padding: 34px 25px; } }
  </style>
</head>
<body>
  <main class="login-shell">
    <section class="welcome-panel" aria-label="MediCare welcome">
      <a class="brand" href="{{ route('login') }}"><span class="brand-mark"><i class="fas fa-plus"></i></span><span>MediCare</span></a>
      <div class="welcome-copy">
        <div class="eyebrow">Care, connected</div>
        <h1>Your work makes a difference.</h1>
        <p>Sign in to your secure care partner account and continue making every visit a little better.</p>
      </div>
      <div class="trust-note"><i class="fas fa-shield-alt"></i> Private, secure access for doctors and hospitals</div>
    </section>
    <section class="form-panel" aria-labelledby="login-heading">
      <div class="form-heading">
        <h2 id="login-heading">Welcome back</h2>
        <p>Enter the username and password provided by your administrator.</p>
      </div>
      <form method="POST" action="{{ route('user.logins') }}" autocomplete="on">
        @csrf
        @if ($errors->any())
          <div class="alert-box" role="alert">{{ $errors->first() }}</div>
        @endif
        @if (session('login_expired'))
          <div class="alert-box" role="alert">{{ session('login_expired') }}</div>
        @endif
        <div class="field">
          <label for="username">Username</label>
          <div class="input-wrap"><i class="far fa-user"></i><input id="username" type="text" name="username" value="{{ old('username') }}" maxlength="8" autocomplete="username" placeholder="Your username" required autofocus></div>
        </div>
        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap"><i class="fas fa-lock"></i><input id="password" type="password" name="password" autocomplete="current-password" placeholder="Your password" required></div>
        </div>
        <div class="form-options">
          <label class="remember" for="remember"><input id="remember" type="checkbox" name="remember" value="1"><span>Keep me signed in</span></label>
        </div>
        <button class="submit-btn" type="submit">Sign in securely <i class="fas fa-arrow-right"></i></button>
        <p class="help-note">Need access? Contact your hospital or clinic administrator.</p>
      </form>
    </section>
  </main>
</body>
</html>
