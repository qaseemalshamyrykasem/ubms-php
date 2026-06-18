<?php

return [
    'uploads_max_size_mb' => env('MAX_UPLOAD_SIZE_MB', 20),
    'uploads_allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'txt', 'csv'],
    'avatar_max_size_mb' => 2,
    'qr_ttl_minutes' => env('ATTENDANCE_QR_TTL_MINUTES', 15),
    'late_threshold_minutes' => env('ATTENDANCE_LATE_THRESHOLD_MINUTES', 15),
];
