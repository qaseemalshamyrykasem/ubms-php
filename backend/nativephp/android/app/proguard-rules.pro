# NativePHP Mobile - ProGuard rules

# Keep NativePHP runtime classes
-keep class com.nativephp.** { *; }
-keep class com.university.ubms.** { *; }

# Keep Capacitor classes (plugin reflection)
-keep class com.getcapacitor.** { *; }
-keep @com.getcapacitor.annotation.CapacitorPlugin class * { *; }
-keep class com.getcapacitor.plugin.** { *; }

# Keep model classes used in JSON serialization
-keepattributes *Annotation*
-keepattributes Signature
-keepattributes EnclosingMethod
-keepclassmembers,allowobfuscation class * {
    @com.alibaba.fastjson.annotation.* <fields>;
    @com.google.gson.annotations.* <fields>;
    @com.fasterxml.jackson.annotation.* <fields>;
}

# WebView
-keepclassmembers class * extends android.webkit.WebViewClient {
    public void *(android.webkit.WebView, java.lang.String, android.graphics.Bitmap);
    public boolean *(android.webkit.WebView, java.lang.String);
    public void *(android.webkit.WebView, java.lang.String);
}

# PHP runtime (do not strip)
-keep class php.** { *; }
-keep class PHPRuntime { *; }

# Kotlin metadata
-keep class kotlin.Metadata { *; }
-dontwarn kotlin.**
