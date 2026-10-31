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

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin: 34px 0 16px;
        }

        .section-heading h2 {
            margin: 0;
            color: #102a43;
            font-size: 1.4rem;
        }

        .button,
        button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 10px 15px;
            border: 0;
            border-radius: 8px;
            background: #1769aa;
            color: #fff;
            font: inherit;
            font-weight: 650;
            text-decoration: none;
            cursor: pointer;
        }

        .button:hover,
        button:hover {
            background: #12588f;
        }

        .button-secondary {
            background: #e8eef5;
            color: #263d53;
        }

        .button-danger {
            background: #a93434;
        }

        .button-danger:hover {
            background: #862727;
        }

        .form-card {
            max-width: 720px;
            padding: 24px;
            border: 1px solid #e0e8f0;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 14px 35px rgb(16 42 67 / 6%);
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .field {
            display: grid;
            gap: 6px;
            margin-bottom: 16px;
            color: #34475b;
            font-size: .92rem;
            font-weight: 650;
        }

        .field-wide {
            grid-column: 1 / -1;
        }

        input,
        select,
        textarea {
            width: 100%;
            min-height: 42px;
            padding: 9px 11px;
            border: 1px solid #cad5e0;
            border-radius: 7px;
            background: #fff;
            color: #172033;
            font: inherit;
            font-weight: 400;
        }

        textarea {
            min-height: 100px;
            resize: vertical;
        }

        input[type="checkbox"] {
            width: 18px;
            min-height: 18px;
        }

        .checkbox-field {
            display: flex;
            align-items: center;
            gap: 9px;
            margin: 0 0 18px;
            color: #34475b;
        }

        .form-actions,
        .inline-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .notice {
            margin: 0 0 18px;
            padding: 12px 15px;
            border-radius: 8px;
            background: #e6f5eb;
            color: #1d6335;
        }

        .error-list {
            margin: 0 0 18px;
            padding: 12px 15px 12px 34px;
            border-radius: 8px;
            background: #fff0ef;
            color: #8d2924;
        }

        .muted {
            color: #718096;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            gap: 14px;
            margin: 20px 0;
        }

        .summary-card {
            padding: 18px;
            border: 1px solid #e0e8f0;
            border-radius: 12px;
            background: #fff;
        }

        .summary-card span {
            display: block;
            margin-bottom: 6px;
            color: #536579;
            font-size: .85rem;
        }

        .summary-card strong {
            color: #102a43;
            font-size: 1.35rem;
        }

        .status {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 999px;
            background: #edf1f5;
            color: #34475b;
            font-size: .82rem;
            white-space: nowrap;
        }

        .status-paid {
            background: #e6f5eb;
            color: #1d6335;
        }

        .status-pending {
            background: #fff4d8;
            color: #805c0a;
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
                <a href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Buku</a>
                <a href="{{ route('genres.index') }}" @if (request()->routeIs('genres.*')) aria-current="page" @endif>Genre</a>
                <a href="{{ route('authors.index') }}" @if (request()->routeIs('authors.*')) aria-current="page" @endif>Author</a>
                @auth
                    @can('access-admin')
                        <a href="{{ route('admin.books.index') }}" @if (request()->routeIs('admin.books.*')) aria-current="page" @endif>Kelola Buku</a>
                        <a href="{{ route('admin.sales.index') }}" @if (request()->routeIs('admin.sales.*')) aria-current="page" @endif>Pesanan</a>
                        <a href="{{ route('admin.reports.index') }}" @if (request()->routeIs('admin.reports.*')) aria-current="page" @endif>Laporan</a>
                    @endcan
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="button-secondary" type="submit">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Admin</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @if (session('status'))
            <p class="notice" role="status">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <ul class="error-list" role="alert">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
        @yield('content')
    </main>

    <footer>Book Sales · Katalog dan penjualan buku.</footer>
</body>
</html>
