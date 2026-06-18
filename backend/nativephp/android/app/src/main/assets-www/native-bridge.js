/**
 * UBMS Native Bridge (compiled to native-bridge.js)
 * Loaded by Blade views to provide native functionality.
 */
(function () {
    'use strict';

    const UBMSNative = {
        isNative: function () {
            return typeof window.Capacitor !== 'undefined' &&
                   window.Capacitor.isNativePlatform &&
                   window.Capacitor.isNativePlatform();
        },

        vibrate: function (duration) {
            duration = duration || 100;
            if (this.isNative() && window.Capacitor.Plugins.Haptics) {
                window.Capacitor.Plugins.Haptics.impact({ style: 'LIGHT', duration: duration })
                    .catch(function () {});
            } else if ('vibrate' in navigator) {
                navigator.vibrate(duration);
            }
        },

        notify: function (title, body) {
            if (this.isNative() && window.Capacitor.Plugins.LocalNotifications) {
                window.Capacitor.Plugins.LocalNotifications.schedule({
                    notifications: [{
                        id: Date.now(),
                        title: title,
                        body: body
                    }]
                }).catch(function () {});
            }
        },

        scanQr: function (callback) {
            // Uses html5-qrcode library loaded in Blade view
            if (typeof Html5Qrcode !== 'undefined') {
                return; // Let the view handle it
            }
            if (this.isNative() && window.Capacitor.Plugins.Camera) {
                window.Capacitor.Plugins.Camera.getPhoto({
                    quality: 90,
                    allowEditing: false,
                    resultType: 'base64',
                    source: 'CAMERA'
                }).then(function (result) {
                    callback(result.base64String);
                }).catch(function (e) {
                    console.warn('Camera failed:', e);
                });
            }
        },

        setStatusBar: function (color) {
            color = color || '#4F46E5';
            if (this.isNative() && window.Capacitor.Plugins.StatusBar) {
                window.Capacitor.Plugins.StatusBar.setBackgroundColor({ color: color })
                    .catch(function () {});
                window.Capacitor.Plugins.StatusBar.setStyle({ style: 'DARK' })
                    .catch(function () {});
            }
        },

        hideSplash: function () {
            if (this.isNative() && window.Capacitor.Plugins.SplashScreen) {
                window.Capacitor.Plugins.SplashScreen.hide().catch(function () {});
            }
        }
    };

    window.UBMSNative = UBMSNative;

    document.addEventListener('DOMContentLoaded', function () {
        UBMSNative.setStatusBar();
        setTimeout(function () { UBMSNative.hideSplash(); }, 1500);
    });
})();
