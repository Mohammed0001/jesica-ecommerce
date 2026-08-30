<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') — @yield('title') | JESSICA RIAD</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    {{-- Deliberately self-contained: an error page must render even when the
         app layout, the database or the asset build is the thing that broke. --}}
    <style>
        :root {
            --ink: #000;
            --muted: #6c757d;
            --line: #ececec;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Futura PT", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-weight: 200;
            color: var(--ink);
            background: #fff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            line-height: 1.6;
        }

        .err {
            max-width: 34rem;
            width: 100%;
            text-align: center;
        }

        .err__code {
            font-size: clamp(3.5rem, 12vw, 6rem);
            font-weight: 200;
            letter-spacing: 0.12em;
            line-height: 1;
            margin-bottom: 1.25rem;
        }

        .err__title {
            font-size: clamp(1.25rem, 4vw, 1.75rem);
            font-weight: 300;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin-bottom: 1rem;
        }

        .err__body {
            color: var(--muted);
            margin-bottom: 1.5rem;
        }

        .err__hints {
            list-style: none;
            text-align: left;
            display: inline-block;
            color: var(--muted);
            font-size: 0.95rem;
            margin-bottom: 2rem;
            border-left: 1px solid var(--line);
            padding-left: 1.25rem;
        }

        .err__hints li + li { margin-top: 0.4rem; }

        .err__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
        }

        .err__btn {
            display: inline-block;
            padding: 0.85rem 2rem;
            border: 1px solid var(--ink);
            background: var(--ink);
            color: #fff;
            text-decoration: none;
            font-size: 0.8rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            transition: background 0.25s ease, color 0.25s ease;
        }

        .err__btn:hover { background: #fff; color: var(--ink); }

        .err__btn--ghost { background: #fff; color: var(--ink); }
        .err__btn--ghost:hover { background: var(--ink); color: #fff; }

        .err__ref {
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--line);
            font-size: 0.8rem;
            color: var(--muted);
            letter-spacing: 0.05em;
        }

        .err__ref code {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            background: #f6f6f6;
            padding: 0.15rem 0.45rem;
        }
    </style>
</head>

<body>
    <main class="err">
        <p class="err__code">@yield('code')</p>
        <h1 class="err__title">@yield('title')</h1>
        <p class="err__body">@yield('message')</p>

        @hasSection('hints')
            <ul class="err__hints">@yield('hints')</ul>
        @endif

        <div class="err__actions">
            @yield('actions')
            <a class="err__btn" href="{{ url('/') }}">Back to home</a>
        </div>

        @hasSection('reference')
            <p class="err__ref">@yield('reference')</p>
        @endif
    </main>
</body>

</html>
