<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        :root {
            --card: rgba(255, 255, 255, 0.94);
            --text: #14213d;
            --muted: #5c6b85;
            --border: #d7e0ee;
            --primary: #f97316;
            --primary-hover: #ea580c;
            --accent: #0f766e;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Trebuchet MS", "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text);
            background:
                linear-gradient(rgba(9, 16, 30, 0.52), rgba(9, 16, 30, 0.6)),
                linear-gradient(120deg, rgba(15, 118, 110, 0.22), rgba(249, 115, 22, 0.18)),
                url("https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1600&q=80") center center / cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .scene {
            position: relative;
            width: 100%;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 32px 20px;
        }

        .scene::before,
        .scene::after {
            content: "";
            position: absolute;
            inset: auto;
            border-radius: 999px;
            filter: blur(10px);
            opacity: 0.75;
        }

        .scene::before {
            width: 320px;
            height: 320px;
            top: -60px;
            right: -80px;
            background: rgba(249, 115, 22, 0.2);
        }

        .scene::after {
            width: 260px;
            height: 260px;
            bottom: -80px;
            left: -60px;
            background: rgba(15, 118, 110, 0.2);
        }

        .workspace-art {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(255, 255, 255, 0.06) 1px, transparent 1px),
                linear-gradient(rgba(255, 255, 255, 0.06) 1px, transparent 1px);
            background-size: 120px 120px;
            opacity: 0.16;
        }

        .workspace-art .panel {
            position: absolute;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.24);
            backdrop-filter: blur(6px);
        }

        .workspace-art .panel-left {
            width: 34%;
            height: 60%;
            left: 4%;
            top: 16%;
            border-radius: 28px;
            transform: rotate(-6deg);
        }

        .workspace-art .panel-right {
            width: 28%;
            height: 52%;
            right: 6%;
            top: 14%;
            border-radius: 28px;
            transform: rotate(7deg);
        }

        .workspace-art .desk {
            position: absolute;
            left: 10%;
            right: 10%;
            bottom: 10%;
            height: 18px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
        }

        .card {
            position: relative;
            width: min(100%, 420px);
            background: var(--card);
            border-radius: 18px;
            padding: 34px 28px 26px;
            box-shadow: 0 30px 70px rgba(15, 23, 42, 0.35);
            z-index: 1;
        }

        .brand-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 14px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(249, 115, 22, 0.12);
            color: var(--primary);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 2rem;
            line-height: 1.1;
            text-align: center;
        }

        .subtext {
            margin: 0 0 28px;
            color: var(--muted);
            text-align: center;
            font-size: 0.95rem;
        }

        .field {
            margin-bottom: 16px;
        }

        .alert-box {
            margin-bottom: 18px;
            padding: 12px 14px;
            border-radius: 10px;
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #be123c;
            font-size: 0.92rem;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.95rem;
            font-weight: 600;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px 15px;
            font-size: 0.98rem;
            color: var(--text);
            background: #fff;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: rgba(249, 115, 22, 0.45);
            box-shadow: 0 0 0 4px rgba(249, 115, 22, 0.12);
        }

        .meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 6px 0 22px;
            font-size: 0.92rem;
        }

        .remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--muted);
        }

        .remember input {
            width: 16px;
            height: 16px;
        }

        .link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .link:hover {
            color: var(--primary-hover);
        }

        .submit-btn {
            width: 100%;
            border: 0;
            border-radius: 10px;
            padding: 14px 18px;
            background: linear-gradient(135deg, #fb923c, #f97316);
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 14px 30px rgba(249, 115, 22, 0.28);
        }

        .submit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 18px 34px rgba(249, 115, 22, 0.34);
        }

        .signup {
            margin: 18px 0 0;
            text-align: center;
            color: var(--muted);
            font-size: 0.95rem;
        }

        @media (max-width: 640px) {
            .scene {
                padding: 18px;
            }

            .card {
                padding: 26px 20px 22px;
                border-radius: 16px;
            }

            h1 {
                font-size: 1.7rem;
            }

            .meta-row {
                flex-direction: column;
                align-items: flex-start;
            }

            .workspace-art .panel-left,
            .workspace-art .panel-right {
                display: none;
            }
        }
    </style>
</head>
<body>
    <main class="scene">
        <div class="workspace-art" aria-hidden="true">
            <div class="panel panel-left"></div>
            <div class="panel panel-right"></div>
            <div class="desk"></div>
        </div>

        <section class="card">
            <div class="brand-tag">Secure Access</div>
            <h1>Administrator sign in</h1>
            <p class="subtext">Sign in to manage your MediCare workspace.</p>

            <form method="POST" id="loginForm" action="{{ route('admin.logins') }}">
                @csrf

                <div id="alert-container"></div>

                @if ($errors->any())
                    <div class="alert-box">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="field">
                    <label for="email">Your email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        placeholder="name@company.com"
                        value="{{ old('email') }}"
                        required
                    >
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        placeholder="Enter your password"
                        required
                    >
                </div>

                <div class="meta-row">
                    <label class="remember" for="remember">
                        <input id="remember" type="checkbox" name="remember">
                        <span>Remember me</span>
                    </label>

                </div>

                <button class="submit-btn" type="submit">Log in to your account</button>

            </form>
        </section>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        var adminLoginUrl = "{{ route('admin.logins') }}";
        var adminIndexUrl = "{{ route('admin.index') }}";
    </script>
    <script src="{{ asset('assets/js/login.js') }}"></script>
</body>
</html>
