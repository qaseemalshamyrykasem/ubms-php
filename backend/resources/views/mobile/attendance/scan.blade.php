@extends('mobile.layouts.app')
@section('title', 'مسح QR - UBMS')

@push('styles')
<style>
    #qr-reader {
        width: 100%;
        max-width: 350px;
        margin: 0 auto;
        border-radius: 16px;
        overflow: hidden;
    }
    #qr-reader video {
        border-radius: 16px;
    }
    .scan-overlay {
        position: relative;
        margin: 20px auto;
        width: 280px;
        height: 280px;
        max-width: 90vw;
        max-height: 90vw;
    }
    .scan-overlay::before, .scan-overlay::after {
        content: '';
        position: absolute;
        width: 50px;
        height: 50px;
        border: 4px solid white;
        border-radius: 4px;
        z-index: 10;
    }
    .scan-overlay::before {
        top: 0; right: 0;
        border-left: none; border-bottom: none;
    }
    .scan-overlay::after {
        bottom: 0; left: 0;
        border-right: none; border-top: none;
    }
    .scan-corners-2 {
        position: absolute;
        width: 50px; height: 50px;
        border: 4px solid white;
        border-radius: 4px;
        z-index: 10;
    }
    .scan-corners-2.tr { top: 0; left: 0; border-right: none; border-bottom: none; }
    .scan-corners-2.bl { bottom: 0; right: 0; border-left: none; border-top: none; }
    .scanning-line {
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--primary), transparent);
        animation: scan 2s linear infinite;
        z-index: 11;
    }
    @keyframes scan {
        0% { top: 0; }
        50% { top: 100%; }
        100% { top: 0; }
    }
</style>
@endpush

@section('content')
<div class="app-bar">
    <a href="/mobile/attendance" class="icon-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    </a>
    <div class="app-bar-title">📷 مسح QR Code</div>
    <div style="width: 36px;"></div>
</div>

<div class="main fade-in" style="text-align: center; padding-top: 24px;">
    <div style="margin-bottom: 16px;">
        <div style="font-size: 48px; margin-bottom: 8px;">🎯</div>
        <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 8px;">وجّه الكاميرا نحو الرمز</h2>
        <p style="color: var(--text-muted); font-size: 13px;">سيتم تسجيل حضورك تلقائياً عند التعرف على الرمز</p>
    </div>

    {{-- QR Reader container --}}
    <div id="qr-reader" style="position: relative; background: black; aspect-ratio: 1; border-radius: 16px;">
        {{-- Camera video will be injected here --}}
        <div id="qr-reader-video" style="width: 100%; height: 100%;"></div>

        {{-- Scan overlay --}}
        <div class="scan-overlay" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); pointer-events: none;">
            <div class="scan-corners-2 tr"></div>
            <div class="scan-corners-2 bl"></div>
            <div class="scanning-line"></div>
        </div>
    </div>

    <div id="status" style="margin-top: 24px; padding: 16px; background: var(--bg-card); border-radius: 12px; font-size: 13px; color: var(--text-muted);">
        <span id="status-icon">⏳</span>
        <span id="status-text">جارٍ تهيئة الكاميرا...</span>
    </div>

    <form id="manual-form" method="POST" action="/mobile/attendance/scan" style="margin-top: 24px; display: none;">
        @csrf
        <div class="form-group">
            <label class="form-label">أو أدخل الرمز يدوياً</label>
            <input type="text" name="token" class="form-input" placeholder="UUID token" style="font-family: monospace; font-size: 12px;">
            <input type="hidden" name="lecture_id" id="lecture-id">
        </div>
        <button type="submit" class="btn btn-outline">إرسال</button>
    </form>

    <button onclick="toggleManual()" class="btn btn-secondary" style="margin-top: 12px; width: auto; display: inline-flex;">
        ⌨️ إدخال يدوي
    </button>
</div>

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let html5QrCode = null;
    let scanning = false;

    function updateStatus(icon, text, type = '') {
        document.getElementById('status-icon').textContent = icon;
        document.getElementById('status-text').textContent = text;
        const status = document.getElementById('status');
        status.className = type === 'success' ? '' : '';
        status.style.background = type === 'success' ? 'rgba(16, 185, 129, 0.2)' :
                                   type === 'error' ? 'rgba(239, 68, 68, 0.2)' : 'var(--bg-card)';
    }

    function startScanner() {
        if (scanning) return;

        updateStatus('⏳', 'جارٍ تهيئة الكاميرا...');

        // Check if NativePHP native camera is available
        if (window.nativephp) {
            window.nativephp.postMessage(JSON.stringify({ type: 'camera.scanQr' }));
            // NativePHP will respond via postMessage back
            window.addEventListener('message', handleNativeQr);
            updateStatus('📷', 'الكاميرا الأصلية جاهزة - وجّه نحو الرمز');
            return;
        }

        // Fallback: HTML5 QR Code library
        if (typeof Html5Qrcode === 'undefined') {
            updateStatus('❌', 'مكتبة المسح غير متاحة', 'error');
            return;
        }

        try {
            html5QrCode = new Html5Qrcode("qr-reader-video");
            scanning = true;

            html5QrCode.start(
                { facingMode: "environment" },
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0
                },
                onScanSuccess,
                onScanFailure
            ).then(() => {
                updateStatus('📷', 'الكاميرا جاهزة - وجّه نحو الرمز');
            }).catch((err) => {
                updateStatus('❌', 'تعذّر الوصول للكاميرا: ' + err, 'error');
                console.error(err);
            });
        } catch (e) {
            updateStatus('❌', 'خطأ: ' + e.message, 'error');
        }
    }

    function handleNativeQr(event) {
        if (event.data && event.data.type === 'qr_result') {
            onScanSuccess(event.data.payload);
        }
    }

    function onScanSuccess(decodedText) {
        if (!scanning && !window.nativephp) return;

        // Vibrate on success
        if (navigator.vibrate) navigator.vibrate(100);
        if (window.nativeVibrate) window.nativeVibrate(100);

        updateStatus('✅', 'تم التعرف على الرمز! جارٍ التحقق...', 'success');

        // Parse the QR payload
        // Expected format: {"lecture_id":N,"token":"uuid","exp":timestamp}
        // Or: ubms://lecture/{id}/qr/{token}
        let lectureId = null;
        let token = decodedText;

        try {
            const payload = JSON.parse(decodedText);
            lectureId = payload.lecture_id;
            token = payload.token;
        } catch (e) {
            // Try URL format
            const match = decodedText.match(/lecture\/(\d+)\/qr\/([a-f0-9-]+)/);
            if (match) {
                lectureId = match[1];
                token = match[2];
            }
        }

        if (!lectureId || !token) {
            updateStatus('❌', 'الرمز غير صالح', 'error');
            setTimeout(() => startScanner(), 2000);
            return;
        }

        // Submit to server
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('lecture_id', lectureId);
        formData.append('token', token);

        fetch('/mobile/attendance/scan', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                updateStatus('🎉', 'تم تسجيل حضورك بنجاح!', 'success');
                if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                setTimeout(() => window.location.href = '/mobile/attendance', 2000);
            } else {
                updateStatus('⚠️', data.message || 'تعذّر التسجيل', 'error');
                if (navigator.vibrate) navigator.vibrate([200, 100, 200]);
                setTimeout(() => startScanner(), 2500);
            }
        })
        .catch(err => {
            updateStatus('❌', 'خطأ في الشبكة', 'error');
            setTimeout(() => startScanner(), 2000);
        });
    }

    function onScanFailure(error) {
        // Silent - this fires many times per second
    }

    function stopScanner() {
        scanning = false;
        if (html5QrCode) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
            }).catch(err => console.error(err));
        }
        if (window.nativephp) {
            window.removeEventListener('message', handleNativeQr);
        }
    }

    function toggleManual() {
        const form = document.getElementById('manual-form');
        form.style.display = form.style.display === 'none' ? 'block' : 'none';
        if (form.style.display === 'block') stopScanner();
        else startScanner();
    }

    // Start scanning on page load
    setTimeout(startScanner, 500);

    // Cleanup on page leave
    window.addEventListener('beforeunload', stopScanner);
</script>
@endpush
@endsection
