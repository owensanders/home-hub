<?php

declare(strict_types=1);

return [
    'disk' => env('DOCUMENTS_DISK', 'local'),

    // Matches the design's storage meter copy ("of 20 GB").
    'quota_bytes' => 20 * 1024 * 1024 * 1024,

    // Matches the design's dropzone copy ("Up to 50MB each").
    'max_upload_kb' => 50 * 1024,
];
