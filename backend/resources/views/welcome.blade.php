<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'UBMS') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 50px;
            border-radius: 20px;
            text-align: center;
            max-width: 600px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
        }
        h1 { margin: 0 0 10px; font-size: 32px; }
        p { opacity: 0.9; line-height: 1.7; }
        .badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 10px;
        }
        code {
            background: rgba(0,0,0,0.3);
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🎓 {{ config('app.name') }}</h1>
        <p>مرحباً بك في نظام إدارة الدفعات الجامعية</p>
        <p>الواجهة البرمجية تعمل بنجاح. للوصول إلى الواجهة الأمامية، يرجى تشغيل خادم React.</p>
        <p>
            <span class="badge">Laravel {{ app()->version() }}</span>
            <span class="badge">PHP {{ PHP_VERSION }}</span>
            <span class="badge">API: <code>/api/v1</code></span>
        </p>
    </div>
</body>
</html>
