# cakephp-swagger

[![Total Downloads](https://img.shields.io/packagist/dt/alt3/cakephp-swagger.svg?style=flat-square)](https://packagist.org/packages/alt3/cakephp-swagger)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.txt)

Swagger plugin for properly documenting your CakePHP 3.x APIs using:

- [swagger-php](https://github.com/zircote/swagger-php)
- [swagger-ui](https://github.com/swagger-api/swagger-ui)


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
            'documents' => []
        ]
    ];
    ```

## Configuration

#### Documents

Specify one or more documents in your configuration file so:

 - swagger-php knows which files and folders to parse for annotations
 - this plugin can serve the results as json (for used in the UI)

```php
'Swagger' => [
    'documents' => [
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
            ]
        ]
    ]
]
```

The above will:

- create two document endpoints:
    - under url `/alt3/swagger/docs`
    - named `api` and `editor`
    - serving swagger json documents
- make swagger-php:
    - scan all files and folders defined in `include` for swagger-php annotations
    - skip all files and folders defined in `exclude`

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
- automatically turn OFF caching when in debug mode
- prevent CORS headers being added to the document responses
- change the page title as used by the UI


## Usage

- http://your.api.com/alt3/swagger/ui
- http://your.api.com/alt3/swagger/docs/<document-endpoint>

> Please note that the UI will automatically load the first document
> specified in the configuration file.

## Contribute

Make sure tests and
[CakePHP Code Sniffer](https://github.com/cakephp/cakephp-codesniffer)
pass before submitting a PR.
