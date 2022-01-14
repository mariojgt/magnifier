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
            //'crop'   => true
        ]
    ],

    'extensions' => [
        'jpg' => [
            'resizable' => true,
            'ctype' => 'image/jpeg',
        ],
        'png' => [
            'resizable' => true,
            'ctype' => 'image/png',
        ],
        'gif' => [
            'resizable' => true,
            'ctype' => 'image/gif',
        ],
        'jpeg' => [
            'resizable' => true,
            'ctype' => 'image/jpeg',
        ],
        'bmp' => [
            'ctype' => 'image/bmp'
        ],
        'pdf' => [
            'ctype' => 'application/pdf'
        ],
        'mp4' => [
            'folder'    => 'expire/',
            'ctype'     => 'video/mp4'
        ],
        'mov' => [
            'folder'    => 'expire/',
            'ctype'     => 'video/quicktime'
        ],
        'mpg' => [
            'folder'    => 'expire/',
            'ctype'     => 'video/mpeg'
        ],
        'avi' => [
            'folder'    => 'expire/',
            'ctype'     => 'video/x-msvideo'
        ]
    ],
    'use_webp'       => true,
    'disk'           => 'public',
    'public_path'    => storage_path(),
    'default_folder' => 'app/public/media/',
    'default_size'   => 'small',
    'img_fall_back'  => '/noimage.jpg', // path is images/imagename.png so the helper knows
    'no_image_id'    => '1',
    'max_size'       => '10048',
    'allowed'        => 'csv,txt,xlx,xls,pdf,jpeg,png,gif,webp',
];
