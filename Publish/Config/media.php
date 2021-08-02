<?php
return [
    'sizes' => [
        'default' => [
            'width'  => 1920,
            'height' => 3000
        ],
        'medium' => [
            'width'  => 1400,
            'height' => 2000
        ],
        'small' => [
            'width'  => 800,
            'height' => 2000
        ],
        'tiny' => [
            'width'  => 400,
            'height' => 800
        ],
        'thumbnail' => [
            'width'  => 150,
            'height' => 300,
        ]
    ],
    'magnifier_middleware' => ['web'],  // Add you guard here web by default
    'allowed_extensions'   => 'csv,txt,xlx,xls,pdf,jpeg,png,gif,webp',
];
