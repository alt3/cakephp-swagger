# cakephp-swagger

[![Build Status](https://img.shields.io/travis/alt3/cakephp-swagger/master.svg?style=flat-square)](https://travis-ci.org/alt3/cakephp-swagger)
[![Coverage](https://img.shields.io/coveralls/alt3/cakephp-swagger/master.svg?style=flat-square)](https://coveralls.io/r/alt3/cakephp-swagger)
[![Total Downloads](https://img.shields.io/packagist/dt/alt3/cakephp-swagger.svg?style=flat-square)](https://packagist.org/packages/alt3/cakephp-swagger)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.txt)

Swagger plugin for documenting your CakePHP 3.x APIs.

## Requirements

* CakePHP 3.0+
* Some [swagger-php](https://github.com/zircote/swagger-php) annotation knowledge

## Installation

1. Install the plugin using composer:

    ```bash
    composer require alt3/cakephp-swagger:dev-master
    ```

2. To enable the plugin either run the following command:

    ```bash
    bin/cake plugin load Alt3/Swagger --routes
    ```

    or manually add the following line to your `config/bootstrap.php` file:

    ```bash
    Plugin::load('Alt3/Swagger', ['routes' => true]);
    ```
3. Browsing to `http://your.app/alt3/swagger` should now produce the
[Swagger-UI](http://swagger.io/swagger-ui/) interface:

    ![Default UI index](/docs/images/ui-index-default.png)

## Configuration

All configuration for this plugin is done through the `/config/swagger.php`
configuration file. TLDR full example below.

```php
<?php
use Cake\Core\Configure;

return [
    'Swagger' => [
        'noCache' => Configure::read('debug'),
        'ui' => [
            'title' => 'ALT3 Swagger',
            'route' => /swagger/',
            'schemes' => ['https', 'http'],
            'showValidator' => true
        ],
        'docs' => [
            'route' => '/swagger/docs/',
            'cors' => [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST',
                'Access-Control-Allow-Headers' => 'X-Requested-With'
            ]
        ],
        'library' => [
            'api' => [
                'include' => ROOT . DS . 'src',
                'exclude' => [
                    '/Editor/'
                ]
            ],
            'editor' => [
                'include' => [
                    ROOT . DS . 'src' . DS . 'Controller' . DS . 'AppController.php',
                    ROOT . DS . 'src' . DS . 'Controller' . DS . 'Editor',
                    ROOT . DS . 'src' . DS . 'Model'
                ]
            ]
        ]
    ]
];
```

### Main section

Use the main section to customize the following options:

- `noCache`: enable to generate new documents on every run instead of
serving from cache, defaults to `true`

### UI section

Use the `ui` section to customize the following options:

- `title`: sets the Swagger-UI page title, defaults to `cakephp-swagger`
- `route`: expose the UI using a custom route, defaults to `/alt3/swagger/`
- `schemes`: array used to specify third field
[used by the UI to generate the BASE URL](https://github.com/alt3/cakephp-swagger/issues/6)
(`host` and `basePath` are fetched realtime), defaults to `null`
- `showValidator`: enable/disable the online validator, defaults to `true`
### Docs section

Use the `docs` section to customize the following options:

- `route`: expose the documents using a custom route, defaults to `/alt3/swagger/docs/`
- `cors`: specify CORS headers to send with the json responses, defaults to `null`

### Library section

Use the `library` section to specify one or multiple swagger documents so:

- swagger-php will know which files and folders to parse for annotations
- swagger-php can produce the swagger json
- this plugin can expose the json at `http://your.app/alt3/swagger/docs/:id`
(so it can be used by the UI)


```php
'library' => [
    'api' => [
        'include' => ROOT . DS . 'src',
        'exclude' => [
            '/Editor/'
        ]
    ],
    'editor' => [
        'include' => [
            ROOT . DS . 'src' . DS . 'Controller' . DS . 'AppController.php',
            ROOT . DS . 'src' . DS . 'Controller' . DS . 'Editor',
            ROOT . DS . 'src' . DS . 'Model'
        ]
    ]
]
```

The above library will result in:

- swagger-php scanning all files and folders defined in `include`
- swagger-php ignoring all files and folders defined in `exclude`
- two document endpoints serving json at:
    - `http://your.app/alt3/swagger/docs/api`
    - `http://your.app/alt3/swagger/docs/editor`

> **Note**: the UI will auto-load the first document found in the library
> section.

## Quickstart Annotation Example

Explaining [swagger-php](https://github.com/zircote/swagger-php)
annotation voodoo is beyond this plugin but to give yourself a head start while
creating your first library document you might want to copy/paste the following
working example into any of your php files.

> **Note**: the weird non-starred syntax ensures
> compatibility with the CakePHP Code Sniffer.

```php
<?php
/**
    @SWG\Swagger(
        basePath="v0",
        host="api.ecloud.app",
        schemes={"http"},
        @SWG\Info(
            title="cakephp-swagger",
            description="Quickstart annotation example",
            termsOfService="http://swagger.io/terms/",
            version="1.0.0"
        )
    )

    @SWG\Get(
        path="/cocktails",
        summary="Retrieve a list of cocktails",
        tags={"cocktail"},
        consumes={"application/json"},
        produces={"application/json"},
        @SWG\Parameter(
            name="sort",
            description="Sort results by field",
            in="query",
            required=false,
            type="string",
            enum={"name", "description"}
        ),
        @SWG\Response(
            response="200",
            description="Successful operation",
            @SWG\Schema(
                type="array",
                ref="#/definitions/Cocktail"
            )
        ),
        @SWG\Response(
            response=429,
            description="Rate Limit Exceeded"
        )
    )

    @SWG\Definition(
        definition="Cocktail",
        required={"name", "description"},
        @SWG\Property(
            property="id",
            type="integer",
            description="CakePHP record id"
        ),
        @SWG\Property(
            property="name",
            type="string",
            description="CakePHP name field"
        ),
        @SWG\Property(
            property="description",
            type="string",
            description="Description of a most tasty cocktail"
        )
    )
*/
```

Which should result in:

![UI Quickstart Example](/docs/images/ui-quickstart-example.png)

## Additional Reading

- [The Swagger Specification](https://github.com/swagger-api/swagger-spec)
- [PHP Annotation Examples](https://github.com/zircote/swagger-php/tree/master/Examples)


## Contribute

Make sure [PHPUnit](http://book.cakephp.org/3.0/en/development/testing.html#running-tests)
and [CakePHP Code Sniffer](https://github.com/cakephp/cakephp-codesniffer)
tests pass before submitting a PR.

