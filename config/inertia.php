<?php

declare(strict_types=1);

return [
    'testing' => [
        // This app keeps its pages in the lower-cased `pages/` directory the
        // Vue starter kit ships with, not Inertia's default `Pages/`.
        'ensure_pages_exist' => true,
        'page_paths' => [resource_path('js/pages')],
        'page_extensions' => ['vue'],
    ],
];
