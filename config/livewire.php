<?php

return [
    'layout' => 'layouts.app',
    'legacy_model_binding' => false,
    'morphs' => [],
    'temporary_file_upload' => [
        'disk' => 'local',
        'rules' => 'file|max:102400', // 100 MB en kilobytes
        'directory' => 'livewire-tmp',
        'max_file_size' => 100 * 1024 * 1024, // 100 MB
    ],
    'render_html_components' => false,
];
