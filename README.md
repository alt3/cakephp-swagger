# Swagger plugin for CakePHP

[![Total Downloads](https://img.shields.io/packagist/dt/alt3/cakephp-swagger.svg?style=flat-square)](https://packagist.org/packages/alt3/cakephp-swagger)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.txt)

Easily add [swagger-ui](https://github.com/swagger-api/swagger-ui) and
[swagger-php](https://github.com/zircote/swagger-php) to your CakePHP (API) application.

## Requirements

* CakePHP 3.0+

## Installation

Install plugin using composer:

```bash
composer require alt3/cakephp-swagger:dev-master
```

To enable the plugin either run:

```bash
bin/cake plugin load Alt3/Swagger --routes
```

or manually add the following line to your `config/bootstrap.php` file:

```bash
Plugin::load('Alt3/Swagger', ['routes' => true]);
```

## Configuration

Override the plugin's default
[settings](https://github.com/alt3/cakephp-swagger/blob/master/src/Controller/AppController.php#L18)
by creating configuration file `/config/swagger.php` similar to the one below.


```php
<?php
return [
    'Swagger' => [
        'include_path' => ROOT . DS . 'src',
        'exclude_paths' => [],
        'ui' => [
            'page_title' => 'cakephp-swagger ',
        ]
    ]
];
```

## Usage (@todo)

- http://your.api.com/alt3/swagger/ui
- http://your.api.com/alt3/swagger/definitions
