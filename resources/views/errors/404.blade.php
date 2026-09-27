{{-- Custom 404 page: matches the app's dark MD3 theme (resources/css/app.css
     tokens hardcoded so the page renders even without built Vite assets).
     The app is reverse-proxied under /sorify, so the top-page link is a plain
     path like the ones in Login.vue. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — {{ __('Not Found') }}</title>
    <style>
        html {
            color-scheme: dark;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
        }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #111318; /* --md-sys-color-surface */
            color: #e2e2e9;             /* --md-sys-color-on-surface */
            -webkit-font-smoothing: antialiased;
        }
        .card {
            text-align: center;
            padding: 3rem 2rem;
        }
        .code {
            font-size: 5rem;
            font-weight: 600;
            line-height: 1;
            color: #adc6ff; /* --md-sys-color-primary */
        }
        .message {
            margin-top: 0.75rem;
            font-size: 1.125rem;
            color: #c4c6d0; /* --md-sys-color-on-surface-variant */
        }
        .home-link {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.625rem 1.5rem;
            border-radius: 9999px;
            background-color: #adc6ff;           /* --md-sys-color-primary */
            color: #112f60;                     /* --md-sys-color-on-primary */
            font-weight: 500;
            text-decoration: none;
        }
        .home-link:hover {
            background-color: #d8e2ff; /* --md-sys-color-primary-container hover approximation */
        }
    </style>
</head>
<body>
    <main class="card" role="main">
        <div class="code">404</div>
        <p class="message">{{ __('Not Found') }}</p>
        <a class="home-link" href="/sorify">{{ __('Go to top page') }}</a>
    </main>
</body>
</html>
