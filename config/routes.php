<?php
use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * Load app-specific configuration file
 */
$config = 'swagger';
$configPath = CONFIG . $config . '.php';
if (file_exists($configPath)) {
    Configure::load($config, 'default');
}

/**
 * Connect routes using configuration file, otherwise use defaults:
 *
 * - UI, defaults to /alt3/swagger
 * - docs, defaults to /alt3/swagger/docs
 * - per library document, defaults to /alt3/swagger/docs/:id
 */
Router::plugin('Alt3/Swagger', function ($routes) {

    // UI route
    if (Configure::read('Swagger.ui.route')) {
        Router::connect(
            Configure::read('Swagger.ui.route'),
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Ui', 'action' => 'index']
        );
    } else {
        Router::connect(
            '/alt3/swagger/',
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Ui', 'action' => 'index']
        );
    }

    // Documents route
    if (Configure::read('Swagger.docs.route')) {
        Router::connect(
            Configure::read('Swagger.docs.route'),
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Docs', 'action' => 'index']
        );

        Router::connect(
            Configure::read('Swagger.docs.route') . ':id',
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Docs', 'action' => 'index'],
            ['id' => '\w+', 'pass' => ['id']]
        );
    } else {
        Router::connect(
            '/alt3/swagger/docs',
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Docs', 'action' => 'index']
        );

        Router::connect(
            '/alt3/swagger/docs/:id',
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Docs', 'action' => 'index'],
            ['id' => '\w+', 'pass' => ['id']]
        );
    }
});
