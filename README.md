# cakephp-swagger

[![Build Status](https://img.shields.io/travis/alt3/cakephp-swagger/master.svg?style=flat-square)](https://travis-ci.org/alt3/cakephp-swagger)
[![StyleCI Status](https://styleci.io/repos/45741948/shield)](https://styleci.io/repos/45741948)
[![Coverage Status](https://img.shields.io/codecov/c/github/alt3/cakephp-swagger/master.svg?style=flat-square)](https://codecov.io/github/alt3/cakephp-swagger)
[![Total Downloads](https://img.shields.io/packagist/dt/alt3/cakephp-swagger.svg?style=flat-square)](https://packagist.org/packages/alt3/cakephp-swagger)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE.txt)

CakePHP 4.x plugin that adds auto-generated Swagger 2.0 documentation to your projects using swagger-php and swagger-ui.

## Requirements

* CakePHP 4.0+
* Some [swagger-php](https://github.com/zircote/swagger-php) annotation knowledge

## Installation

Install the plugin using composer:

```bash
composer require alt3/cakephp-swagger
```

## Enabling 
Enable the plugin in the `bootstrap()` method found in `src/Application.php`:

```php
    public function bootstrap()
    {
        parent::bootstrap();
        $this->addPlugin('Alt3/Swagger');
    }
```

> Also make sure that AssetMiddleware is loaded inside `Application.php` or all Swagger page assets will 404.

## Installation check

After enabling the plugin, browsing to `http://your.app/alt3/swagger` should now produce the
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
        'ui' => [
            'title' => 'ALT3 Swagger',
            'validator' => true,
            'api_selector' => true,
            'route' => '/swagger/',
            'schemes' => ['http', 'https']
        ],
        'docs' => [
            'crawl' => Configure::read('debug'),
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

### UI section

Use the `ui` section to customize the following Swagger-UI options:

- `title`: sets the Swagger-UI page title, defaults to `cakephp-swagger`
- `validator`: show/hide the validator image, defaults to `true`
- `api_selector`: show/hide the api selector form fields, defaults to `true`
- `route`: expose the UI using a custom route, defaults to `/alt3/swagger/`
- `schemes`: array used to specify third field
[used to generate the BASE URL](https://github.com/alt3/cakephp-swagger/issues/6)
(`host` is fetched realtime, `basePath` is also fetched realtime if not
[defined via annotations](https://github.com/alt3/cakephp-swagger/issues/29)),
defaults to `null`

> Please note that the UI will auto-load the first document found in the library.

### Docs section

Use the `docs` section to customize the following options:

- `crawl`: enable to crawl-generate new documents instead of
serving from filesystem, defaults to `true`
- `route`: expose the documents using a custom route, defaults to `/alt3/swagger/docs/`
- `cors`: specify CORS headers to send with the json responses, defaults to `null`

### Library section

Use the `library` section to specify one or multiple swagger documents so:

- swagger-php will know which files and folders to parse for annotations
- swagger-php can produce the swagger json
- this plugin can expose the json at `http://your.app/alt3/swagger/docs/:id`
(so it can be used by the UI)

The following library example would result in:

- swagger-php scanning all files and folders defined in `include`
- swagger-php ignoring all files and folders defined in `exclude`
- two endpoints serving json swagger documents:
    - `http://your.app/alt3/swagger/docs/api`
    - `http://your.app/alt3/swagger/docs/editor`

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

It would also make `http://your.app/alt3/swagger/docs` produce a json list
with links to all available documents similar to the example below.

```json
{
    "success": true,
    "data": [
        {
            "document": "api",
            "link": "http://your.app/alt3/swagger/docs/api"
        },
        {
            "document": "editor",
            "link": "http://your.app/alt3/swagger/docs/editor"
        }
    ]
}
```

## SwaggerShell

This plugin comes with a shell that should simplify deployment in production
environments. Simply run the following command to create `cakephp_swagger`
prefixed filesystem documents in `tmp/cache` for all entities found in your
library.

```bash
bin/cake swagger makedocs <host>
```

> The host argument (e.g. your.app.com) is required, should not include
protocols and is used to set the `host` property inside your swagger documents.

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
                type="object",
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
- [Swagger Document Checklist](http://apievangelist.com/2015/06/15/my-minimum-viable-definition-for-a-complete-swagger-api-definition/)

## Contribute

Before submitting a PR make sure:

- [PHPUnit](http://book.cakephp.org/4.0/en/development/testing.html#running-tests)
and [CakePHP Code Sniffer](https://github.com/cakephp/cakephp-codesniffer) tests pass
- [Coveralls Code Coverage ](https://coveralls.io/github/alt3/cakephp-swagger) remains at 100%
