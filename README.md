![image info](https://raw.githubusercontent.com/mariojgt/magnifier/main/Publish/Public/image/magnifier_new.png)
# Magnifier

Laravle media manager

# Features

- [ ] Clean media library
- [ ] Fuly integrated with laravel
- [ ] vue js 3
- [ ] no extra dependencies
- [ ] tailwind
- [ ] dynamic guard protection
- [ ] dynamic allowed extension
- [ ] Dark|light mode out of the box
- [ ] Auto image resize on request
- [ ] Universal route for file request
- [ ] Easy to integrate with any laravel project
- [ ] Api based

### How to install.

First you will need to run the migrations.

```art
composer require mariojgt/magnifier

php artisan migrate

php artisan storage:link
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

