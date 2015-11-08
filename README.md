# Swagger plugin for CakePHP

[![Total Downloads](https://img.shields.io/packagist/dt/alt3/cakephp-swagger.svg?style=flat-square)](https://packagist.org/packages/alt3/cakephp-swagger)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.txt)

Easily add [swagger-ui](https://github.com/swagger-api/swagger-ui) and
[swagger-php](https://github.com/zircote/swagger-php) to your CakePHP (API) application.

## Requirements

* CakePHP 3.0+

## Installation

1. Install the plugin using composer:

    ```bash
    composer require alt3/cakephp-swagger:dev-master
    ```

2. To enable the plugin either run:

    ```bash
    bin/cake plugin load Alt3/Swagger --routes
    ```

    or manually add the following line to your `config/bootstrap.php` file:

    ```bash
    Plugin::load('Alt3/Swagger', ['routes' => true]);
    ```

3. Make sure to create configuration file `/config/swagger.php` with at least
the following content:

    ```php
    <?php
    return [
        'Swagger' => [
            'definitions' => []
        ]
    ];
    ```

## Configuration

#### Definitions

Definitions contain information required by:

 - swagger-php to know which files and folders to parse for annotations
 - this plugin to serve the resulting json for usage by the UI

Ccakephp-swagger supports multiple swagger definitions so define as many as
you need.

```php
'Swagger' => [
    'definitions' => [
        'api' => [
            'include' => ROOT . DS . 'src',
            'exclude' => [
                '/Editor/'
            ]
        ],
        'editor' => [
            'include' => [
                ROOT . DS . 'src' . DS . 'Controller' . DS . 'Editor',
                ROOT . DS . 'src' . 'Model'
            ],
            'exclude' => []
        ]
    ]
]
```

The above will:

- create two definition endpoints named `api` and `editor` serving swagger json
- make swagger-php:
    - scan all files and folders defined inside `include` for swagger-php annotations
    - skip all files and folders defined inside `exclude`

### Customization

Use your configuration file to override any of the plugin's default
[settings](https://github.com/alt3/cakephp-swagger/blob/master/src/Controller/AppController.php#L25).

```php
'Swagger' => [
    'noCache' => Configure::read('debug'),
    'cors_headers' => [],
    'ui' => [
        'page_title' => 'My Swagger Docs'
    ]
]
```

The above will:
- turn OFF caching when in debug mode
- no longer set CORS headers for the definition responses
- change the page title used for the UI


## Usage

- http://your.api.com/alt3/swagger/ui
- http://your.api.com/alt3/swagger/definitions/<definition-endpoint>

> Please note that the UI will automatically load the first definition found
> in the configuration file.

## Contribute

Make sure tests and
[CakePHP Code Sniffer](https://github.com/cakephp/cakephp-codesniffer)
pass before submitting a PR.
