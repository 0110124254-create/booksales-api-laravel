<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Book Sales</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #172033;
            background: #f3f6fb;
        }

        * {
            box-sizing: border-box;
        }

        body {
            min-width: 320px;
            margin: 0;
        }

        .topbar {
            background: #102a43;
            color: #fff;
        }

        .topbar-inner,
        main {
            width: min(100% - 40px, 1080px);
            margin: 0 auto;
        }

        .topbar-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            min-height: 76px;
        }

        .brand {
            color: #fff;
            font-size: 1.05rem;
            font-weight: 750;
            letter-spacing: .02em;
            text-decoration: none;
        }

        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        nav a {
            padding: 9px 13px;
            border-radius: 8px;
            color: #d9e7f5;
            font-size: .92rem;
            text-decoration: none;
        }

        nav a:hover,
        nav a[aria-current="page"] {
            background: #244765;
            color: #fff;
        }

        main {
            padding: 52px 0 64px;
        }

        .eyebrow {
            margin: 0 0 10px;
            color: #3974a8;
            font-size: .78rem;
            font-weight: 750;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            color: #102a43;
            font-size: clamp(2rem, 5vw, 2.8rem);
            letter-spacing: -.04em;
        }

        .intro {
            max-width: 650px;
            margin: 12px 0 28px;
            color: #536579;
            line-height: 1.65;
        }

        .catalog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }

        .catalog-link {
            display: block;
            padding: 22px;
            border: 1px solid #e0e8f0;
            border-radius: 14px;
            background: #fff;
            color: inherit;
            text-decoration: none;
            box-shadow: 0 14px 35px rgb(16 42 67 / 6%);
            transition: border-color .15s ease, transform .15s ease;
        }

        .catalog-link:hover {
            transform: translateY(-2px);
            border-color: #91b6d6;
        }

        .catalog-link strong {
            display: block;
            margin-bottom: 7px;
            color: #102a43;
            font-size: 1.08rem;
        }

        .catalog-link span {
            color: #536579;
            font-size: .92rem;
            line-height: 1.5;
        }

        .table-card {
            overflow: hidden;
            border: 1px solid #e0e8f0;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 14px 35px rgb(16 42 67 / 6%);
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th,
        td {
            padding: 16px 20px;
            border-bottom: 1px solid #edf1f5;
            vertical-align: top;
        }

        th {
            background: #f8fafc;
            color: #536579;
            font-size: .76rem;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        td {
            color: #34475b;
            font-size: .94rem;
            line-height: 1.5;
        }

        tbody tr:last-child td {
            border-bottom: 0;
        }

        .number {
            color: #7b8b9b;
            font-variant-numeric: tabular-nums;
        }

        .name {
            color: #102a43;
            font-weight: 700;
        }

        footer {
            padding: 20px;
            color: #718096;
            font-size: .82rem;
            text-align: center;
        }

        @media (max-width: 600px) {
            .topbar-inner {
                align-items: flex-start;
                flex-direction: column;
                gap: 10px;
                padding: 18px 0;
            }

            main {
                padding-top: 36px;
            }

            th,
            td {
                padding: 13px 15px;
            }
        }
    </style>
</head>
<body>
    <header class="topbar">
        <div class="topbar-inner">
            <a class="brand" href="{{ route('home') }}">Book Sales</a>
            <nav aria-label="Navigasi utama">
                <a href="{{ route('genres.index') }}" @if (request()->routeIs('genres.*')) aria-current="page" @endif>Genre</a>
                <a href="{{ route('authors.index') }}" @if (request()->routeIs('authors.*')) aria-current="page" @endif>Author</a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>Contoh penerapan Model, View, dan Controller pada Laravel.</footer>
</body>
</html>
