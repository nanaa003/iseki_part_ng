<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Leader - Iseki Part NG')</title>
    <meta name="description" content="Leader Panel - Iseki Part NG Management System">
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/favicon.svg') }}">
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/inter.css') }}" rel="stylesheet">
    <!-- Dynamic Favicon -->
    <script src="/iseki_pro_app/js/dynamic-favicon.js"></script>
    <script>document.addEventListener("DOMContentLoaded", function() { setDynamicFavicon("settings_alert", "Part NG"); });</script>

    <!-- Dynamic Favicon Assets -->
    <link rel="stylesheet" href="/iseki_pro_app/css/icon.css">
    <script src="/iseki_pro_app/js/dynamic-favicon.js"></script>
    <script>document.addEventListener("DOMContentLoaded", function() { setDynamicFavicon("settings_alert", "Part NG"); });</script>
    <style>
        :root {
            --pink-50: #fff0f6;
            --pink-100: #ffcce1;
            --pink-200: #ffb8d9;
            --pink-300: #ff99c3;
            --pink-400: #ff4d9f;
            --pink-500: #ff66a6;
            --pink-600: #ff3388;
            --pink-700: #ff006a;
            --pink-800: #d10057;
            --pink-900: #a30044;
            --glass-bg: rgba(255, 255, 255, .92);
            --glass-border: rgba(255, 255, 255, .4)
        }

        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif
        }

        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, var(--pink-50) 0%, #fff1f5 50%, #fce7f3 100%);
            min-height: 100vh
        }

        /* SIDEBAR LAYOUT (All Pages) */
        .app-wrapper {
            display: flex;
            min-height: 100vh
        }

        .app-sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--pink-700) 0%, var(--pink-800) 40%, var(--pink-900) 100%);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(157, 23, 77, .15)
        }

        .app-sidebar-brand {
            padding: 1.5rem 1.5rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, .1)
        }

        .app-sidebar-brand .brand-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .12);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: .75rem;
            font-size: 1.3rem;
            color: #fff
        }

        .app-sidebar-brand .brand-text {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            line-height: 1.2
        }

        .app-sidebar-brand .brand-sub {
            font-size: .7rem;
            color: rgba(255, 255, 255, .5);
            font-weight: 400
        }

        .app-sidebar-nav {
            flex: 1;
            padding: .75rem 0;
            overflow-y: auto
        }

        .app-sidebar-nav .nav-section {
            font-size: .65rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: rgba(255, 255, 255, .35);
            padding: .75rem 1.5rem .25rem
        }

        .app-sidebar-nav .nav-link {
            color: rgba(255, 255, 255, .65);
            padding: .7rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            text-decoration: none;
            transition: all .2s;
            border-left: 3px solid transparent;
            font-size: .875rem;
            font-weight: 500
        }

        .app-sidebar-nav .nav-link i {
            font-size: 1.15rem;
            width: 22px;
            text-align: center
        }

        .app-sidebar-nav .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, .08);
            border-left-color: var(--pink-300)
        }

        .app-sidebar-nav .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, .12);
            border-left-color: var(--pink-200);
            font-weight: 600
        }

        .app-sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, .08)
        }

        .app-main {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            width: calc(100% - 260px)
        }

        .app-content {
            flex: 1;
            padding: 2rem
        }

        .app-content h1 {
            font-weight: 800;
            color: var(--pink-800);
            font-size: 1.6rem
        }

        @media(max-width:992px) {
            .app-sidebar {
                width: 220px;
                transform: translateX(-100%);
                transition: transform .3s
            }

            .app-sidebar.open {
                transform: translateX(0)
            }

            .app-main {
                margin-left: 0;
                width: 100%
            }

            .app-mobile-toggle {
                display: flex !important
            }
        }

        .app-mobile-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1100;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--pink-500), var(--pink-600));
            color: #fff;
            border: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(236, 72, 153, .3)
        }

        /* LOGOUT BUTTON — Clear & Visible */
        .btn-logout-sidebar {
            display: flex;
            align-items: center;
            gap: .5rem;
            width: 100%;
            padding: .6rem 1rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .1);
            color: rgba(255, 255, 255, .7);
            font-weight: 600;
            font-size: .8rem;
            transition: all .2s;
            text-decoration: none
        }

        .btn-logout-sidebar:hover {
            background: rgba(239, 68, 68, .2);
            border-color: rgba(239, 68, 68, .3);
            color: #fff
        }

        .btn-logout-sidebar i {
            font-size: 1rem
        }

        /* Shared Pink Utilities */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(219, 39, 119, .08);
            transition: all .3s
        }

        .glass-card:hover {
            box-shadow: 0 12px 40px rgba(219, 39, 119, .15);
            transform: translateY(-2px)
        }

        .btn-pink {
            background: linear-gradient(135deg, var(--pink-500), var(--pink-600));
            border: none;
            color: #fff;
            font-weight: 600;
            border-radius: 12px;
            padding: .75rem 1.5rem;
            transition: all .3s;
            box-shadow: 0 4px 14px rgba(236, 72, 153, .3)
        }

        .btn-pink:hover {
            background: linear-gradient(135deg, var(--pink-600), var(--pink-700));
            box-shadow: 0 6px 20px rgba(236, 72, 153, .4);
            transform: translateY(-1px);
            color: #fff
        }

        .card-header-pink {
            background: linear-gradient(135deg, var(--pink-500), var(--pink-600));
            color: #fff;
            font-weight: 700;
            border-radius: 16px 16px 0 0 !important;
            padding: 1rem 1.25rem;
            border: none
        }

        .table-premium {
            width: 100%;
            border-collapse: collapse
        }

        .table-premium thead th {
            font-weight: 700;
            font-size: .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            padding: .9rem 1rem;
            color: #fff;
            background: linear-gradient(135deg, var(--pink-500), var(--pink-600));
            border: none
        }

        .table-premium thead th:first-child {
            padding-left: 1.5rem
        }

        .table-premium thead th:last-child {
            padding-right: 1.5rem
        }

        .table-premium tbody td {
            padding: .85rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--pink-50);
            font-size: .875rem;
            background: #fff;
            transition: all .15s
        }

        .table-premium tbody tr:hover td {
            background: var(--pink-50)
        }

        .table-premium tbody tr td:first-child {
            padding-left: 1.5rem;
            font-weight: 600;
            color: var(--pink-700);
            border-left: 3px solid transparent;
            transition: all .2s
        }

        .table-premium tbody tr:hover td:first-child {
            border-left-color: var(--pink-400)
        }

        .table-premium tbody tr td:last-child {
            padding-right: 1.5rem
        }

        .table-premium tbody tr:nth-child(even) td {
            background: rgba(255, 255, 255, .5)
        }

        .table-premium tbody tr:nth-child(even):hover td {
            background: var(--pink-50)
        }

        .table-premium tbody tr:last-child td {
            border-bottom: none
        }

        .table-responsive {
            border-radius: 16px;
            border: 1px solid var(--pink-100)
        }

        .table-responsive::-webkit-scrollbar {
            height: 6px
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--pink-300);
            border-radius: 3px
        }

        .pagination .page-link {
            border: none;
            border-radius: 10px;
            margin: 0 2px;
            font-weight: 600;
            font-size: .8rem;
            color: var(--pink-600);
            padding: .45rem .85rem;
            transition: all .2s
        }

        .pagination .page-link:hover {
            background: var(--pink-100);
            color: var(--pink-700);
            transform: translateY(-1px)
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--pink-500), var(--pink-600));
            color: #fff;
            box-shadow: 0 4px 12px rgba(236, 72, 153, .3)
        }

        .pagination .page-item.disabled .page-link {
            background: transparent;
            color: var(--pink-200)
        }

        .badge-pink {
            background: linear-gradient(135deg, var(--pink-500), var(--pink-600));
            color: #fff;
            font-weight: 700;
            padding: .35rem .9rem;
            border-radius: 50px;
            font-size: .75rem;
            letter-spacing: .02em;
            box-shadow: 0 2px 8px rgba(236, 72, 153, .2)
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--pink-400);
            box-shadow: 0 0 0 3px rgba(244, 114, 182, .15)
        }

        .fade-in {
            animation: fadeIn .5s ease-out
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        .slide-up {
            animation: slideUp .6s ease-out
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px)
            }

            to {
                opacity: 1;
                transform: translateY(0)
            }
        }

        ::-webkit-scrollbar {
            width: 6px
        }

        ::-webkit-scrollbar-track {
            background: var(--pink-50)
        }

        ::-webkit-scrollbar-thumb {
            background: var(--pink-300);
            border-radius: 3px
        }

        .footer-app {
            background: linear-gradient(135deg, var(--pink-800), var(--pink-900));
            color: rgba(255, 255, 255, .7);
            padding: 1.5rem 0;
            margin-top: auto
        }

        @yield('styles')
    </style>
    @yield('head')
</head>

<body>
    {{-- Mobile Toggle --}}
    <button class="app-mobile-toggle" onclick="document.querySelector('.app-sidebar').classList.toggle('open')">
        <i class="bi bi-list"></i>
    </button>

    {{-- Sidebar --}}
    <aside class="app-sidebar">
        <div class="app-sidebar-brand d-flex align-items-center">
            <span class="brand-icon"><i class="bi bi-clipboard2-data"></i></span>
            <div class="brand-text">Leader Panel<span class="d-block brand-sub">Iseki Part NG</span></div>
        </div>
        <nav class="app-sidebar-nav">
            <div class="nav-section">Menu</div>
            <a class="nav-link {{ request()->routeIs('leader.dashboard') ? 'active' : '' }}" href="{{ route('leader.dashboard') }}">
                <i class="bi bi-speedometer2"></i><span>Dashboard</span>
            </a>
            <a class="nav-link {{ request()->routeIs('leader.create') ? 'active' : '' }}" href="{{ route('leader.create') }}">
                <i class="bi bi-plus-circle"></i><span>Input Part NG</span>
            </a>
            <a class="nav-link {{ request()->routeIs('leader.report.monthly') ? 'active' : '' }}" href="{{ route('leader.report.monthly') }}">
                <i class="bi bi-calendar-month"></i><span>Laporan</span>
            </a>
            <a class="nav-link {{ request()->routeIs('leader.report.unprocessed') ? 'active' : '' }}" href="{{ route('leader.report.unprocessed') }}">
                <i class="bi bi-hourglass-split"></i><span>Belum Diproses</span>
            </a>
            <a class="nav-link {{ request()->routeIs('leader.report.processed') ? 'active' : '' }}" href="{{ route('leader.report.processed') }}">
                <i class="bi bi-check-all"></i><span>Sudah Diproses</span>
            </a>
        </nav>
        <div class="app-sidebar-footer">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.12);display:flex;align-items:center;justify-content:center;color:#fff;font-size:.85rem">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="text-white small fw-semibold">{{ auth()->user()->Name_User ?? 'Leader' }}</div>
                    <div class="text-white-50" style="font-size:.65rem">Leader</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">@csrf
                <button type="submit" class="btn-logout-sidebar">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="app-main">
        <main class="app-content">
            <div class="fade-in">@yield('content')</div>
        </main>
        <footer class="footer-app">
            <div class="container-fluid px-lg-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div><i class="bi bi-clipboard2-data me-2"></i>Iseki_Part_Ng — Iseki Indonesia {{ date('Y') }}</div>
                    <div class="small opacity-75">&copy; Iseki Indonesia {{ date('Y') }}.</div>
                </div>
            </div>
        </footer>
    </div>

    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    @stack('scripts')
</body>

</html>
