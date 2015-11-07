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

To enable the plugin automatically run:

```bash
bin/cake plugin load Alt3/Swagger --routes
```

Or manually update your `config/bootstrap.php` file with:

```bash
Plugin::load('Alt3/Swagger', ['routes' => true]);
```

## Usage (@todo)

- http://your.api.com/alt3/swagger/ui
- http://your.api.com/alt3/swagger/definitions
