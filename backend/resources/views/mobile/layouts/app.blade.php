<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#4f46e5">
    <meta name="nativephp" content="true">
    <title>@yield('title', 'UBMS')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg: #0f172a;
            --bg-card: #1e293b;
            --bg-elevated: #334155;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --primary: #6366f1;
            --primary-light: #818cf8;
            --accent: #8b5cf6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --border: #334155;
            --safe-top: env(safe-area-inset-top, 0px);
            --safe-bottom: env(safe-area-inset-bottom, 0px);
        }

        * {
            -webkit-tap-highlight-color: transparent;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            font-family: 'Tajawal', 'Cairo', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            overscroll-behavior: none;
        }

        body {
            min-height: 100vh;
            padding-top: var(--safe-top);
            padding-bottom: var(--safe-bottom);
        }

        .app-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .app-bar {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .app-bar-title {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .app-bar-actions { display: flex; align-items: center; gap: 12px; }

        .icon-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
        }

        .badge-dot {
            position: absolute;
            top: 4px; right: 4px;
            background: var(--danger);
            color: white;
            font-size: 10px;
            font-weight: 700;
            min-width: 16px;
            height: 16px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
        }

        .main {
            flex: 1;
            padding: 16px;
            padding-bottom: 90px;
            max-width: 480px;
            margin: 0 auto;
            width: 100%;
        }

        .card {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 12px;
            border: 1px solid var(--border);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .card-title { font-size: 16px; font-weight: 700; }
        .card-body { color: var(--text-muted); font-size: 13px; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 16px;
        }

        .stat-card {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 16px;
        }

        .stat-icon {
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 8px;
            font-size: 20px;
        }

        .stat-value { font-size: 24px; font-weight: 900; }
        .stat-label { color: var(--text-muted); font-size: 12px; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-general { background: rgba(107, 114, 128, 0.2); color: #9ca3af; }
        .badge-urgent { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }
        .badge-emergency { background: rgba(220, 38, 38, 0.2); color: #fca5a5; }
        .badge-important { background: rgba(249, 115, 22, 0.2); color: #fdba74; }
        .badge-lecture { background: rgba(139, 92, 246, 0.2); color: #c4b5fd; }
        .badge-holiday { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; }
        .badge-assignment { background: rgba(59, 130, 246, 0.2); color: #93c5fd; }
        .badge-schedule { background: rgba(245, 158, 11, 0.2); color: #fcd34d; }
        .badge-meeting { background: rgba(6, 182, 212, 0.2); color: #67e8f9; }
        .badge-success { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; }
        .badge-warning { background: rgba(245, 158, 11, 0.2); color: #fcd34d; }
        .badge-danger { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }

        .bottom-nav {
            position: fixed;
            bottom: 0; left: 0; right: 0;
            background: var(--bg-card);
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-around;
            padding: 8px 0 calc(8px + var(--safe-bottom));
            z-index: 50;
            max-width: 480px;
            margin: 0 auto;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2px;
            padding: 8px 12px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 10px;
            font-weight: 500;
            border-radius: 12px;
            position: relative;
        }

        .nav-item.active { color: var(--primary); }
        .nav-item svg { width: 22px; height: 22px; }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 20px;
            border-radius: 12px;
            border: none;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            width: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: white;
        }

        .btn-secondary { background: var(--bg-elevated); color: var(--text); }
        .btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }
        .btn-danger { background: var(--danger); color: white; }

        .form-group { margin-bottom: 14px; }
        .form-label {
            display: block;
            margin-bottom: 6px;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
        }

        .form-input {
            width: 100%;
            padding: 14px 16px;
            background: var(--bg-elevated);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            font-family: inherit;
            font-size: 14px;
        }

        .form-input:focus { outline: none; border-color: var(--primary); }

        .list-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            background: var(--bg-card);
            border-radius: 12px;
            margin-bottom: 8px;
            border: 1px solid var(--border);
            text-decoration: none;
            color: inherit;
        }

        .list-item-content { flex: 1; min-width: 0; }
        .list-item-title {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .list-item-subtitle { font-size: 12px; color: var(--text-muted); }

        .progress {
            height: 8px;
            background: var(--bg-elevated);
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--success), var(--primary));
            transition: width 0.3s;
        }

        .toast {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--bg-elevated);
            color: var(--text);
            padding: 12px 20px;
            border-radius: 12px;
            z-index: 1000;
            opacity: 0;
            transition: opacity 0.3s;
            max-width: 90%;
        }

        .toast.show { opacity: 1; }
        .toast.success { background: var(--success); color: white; }
        .toast.error { background: var(--danger); color: white; }

        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: var(--text-muted);
        }

        .empty-state-icon { font-size: 48px; margin-bottom: 12px; opacity: 0.5; }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            margin: 20px 0 10px;
            padding: 0 4px;
        }

        .fade-in { animation: fadeIn 0.3s ease; }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        form { width: 100%; }
        [x-cloak] { display: none !important; }

        .qr-scanner {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            z-index: 100;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .qr-scanner.active { display: flex; }
        .qr-frame {
            width: 250px;
            height: 250px;
            border: 3px solid var(--primary);
            border-radius: 16px;
            position: relative;
        }
    </style>

    @stack('styles')
    @livewireStyles
</head>
<body>
    <div class="app-container">
        @yield('content')
    </div>

    <div id="toast" class="toast"></div>

    @livewireScripts
    @stack('scripts')

    <script>
        window.showToast = function(msg, type = '') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            toast.className = 'toast show ' + type;
            setTimeout(() => toast.className = 'toast', 3000);
        };

        // Trigger native vibration
        window.nativeVibrate = function(ms = 100) {
            if (window.nativephp) {
                window.nativephp.postMessage(JSON.stringify({ type: 'vibrate', ms }));
            }
        };
    </script>
</body>
</html>
