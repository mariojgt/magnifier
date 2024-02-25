![image info](https://raw.githubusercontent.com/mariojgt/magnifier/main/Publish/Public/image/logo.png)
# Magnifier

Laravel media manager, the aim of this project is to make it easy to manage media files, this project comes with a fully media library with ui, api and more out of the box stop wasting your time with media library and start using it.

# Features

- [ ] Clean media library
- [ ] Fuly integrated with laravel
- [ ] vue js 3
- [ ] no extra dependencies
- [ ] tailwind
- [ ] dynamic guard protection
- [ ] dynamic allowed extension
- [ ] Auto image resize on request
- [ ] Universal route for file request
- [ ] Easy to integrate with any laravel project
- [ ] Api based
- [ ] Vite support
- [ ] Plug and play

### How to install.

First you will need to run the migrations.

```art
composer require mariojgt/magnifier

php artisan install:magnifier
```

Once the migration is done you can access the route /magnifier, to protect the routes using a login route you will need to change the config file media.php in the config folder, you can also add some custom sizes.

```php
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
    'magnifier_middleware' => ['web'],  // Add your middlewhere in here
    'allowed_extensions'   => 'csv,txt,xlx,xls,pdf,jpeg,png,gif,webp',
];
```

