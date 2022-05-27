tester.soketi.app
=================

![CI](https://github.com/soketi/tester/workflows/CI/badge.svg?branch=master)
[![codecov](https://codecov.io/gh/soketi/tester/branch/master/graph/badge.svg?token=b12HgFrUV3)](https://codecov.io/gh/soketi/tester)

Test your Soketi implementation with this awesome web tool. 🥰

## 🙌 Requirements

- PHP 8.1+
- Soketi 1.x+

## 🚀 Installation

```bash
$ composer install --ignore-platform-reqs && \
    cp .env.example .env && \
    php artisan key:generate && \
    touch database/database.sqlite && \
    php artisan migrate && \
    npm install && \
    npm run dev
```

## Running with Homestead or Sail

If you are not having PHP 8.1+ or Redis installed, you can use [Laravel Homestead](https://laravel.com/docs/master/homestead#per-project-installation) or [Laravel Sail for Docker](https://laravel.com/docs/master/sail) as an alternative.

## 🐛 Testing

``` bash
$ vendor/bin/phpunit
```

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒  Security

If you discover any security related issues, please email alex@renoki.org instead of using the issue tracker.

## 🎉 Credits

- [Alex Renoki](https://github.com/rennokki)
- [All Contributors](../../contributors)
