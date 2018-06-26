<?php

use Cake\Core\Configure;
use Cake\Routing\Router;

/*
 * Load app-specific configuration file
 */
$config = 'swagger';
$configPath = CONFIG . $config . '.php';
if (file_exists($configPath)) {
    Configure::load($config, 'default');
}

/*
 * Connect routes using configuration file, otherwise use defaults:
 *
 * - UI, defaults to /alt3/swagger
 * - docs, defaults to /alt3/swagger/docs
 * - per library document, defaults to /alt3/swagger/docs/:id
 */
Router::plugin('Alt3/Swagger', function (\Cake\Routing\RouteBuilder $routes) {

    // UI route
    if (Configure::read('Swagger.ui.route')) {
        $routes->get(
            Configure::read('Swagger.ui.route'),
            ['controller' => 'Ui', 'action' => 'index']
        );
    } else {
        $routes->get(
            '/alt3/swagger/',
            ['controller' => 'Ui', 'action' => 'index']
        );
    }

    // Documents route
    if (Configure::read('Swagger.docs.route')) {
        $routes->get(
            Configure::read('Swagger.docs.route'),
            ['controller' => 'Docs', 'action' => 'index']
        );

        $routes->get(
            Configure::read('Swagger.docs.route') . ':id',
            ['controller' => 'Docs', 'action' => 'index']
        )
            ->setPass(['id'])
            ->setPatterns(['id' => '\w+']);
    } else {
        $routes->get(
            '/alt3/swagger/docs',
            ['controller' => 'Docs', 'action' => 'index']
        );

        $routes->get(
            '/alt3/swagger/docs/:id',
            ['controller' => 'Docs', 'action' => 'index']
        )
            ->setPass(['id'])
            ->setPatterns(['id' => '\w+']);
    }
});
