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
        $routes->connect(
            Configure::read('Swagger.ui.route'),
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Ui', 'action' => 'index']
        );
    } else {
        $routes->connect(
            '/alt3/swagger/',
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Ui', 'action' => 'index']
        );
    }

    // Documents route
    if (Configure::read('Swagger.docs.route')) {
        $routes->connect(
            Configure::read('Swagger.docs.route'),
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Docs', 'action' => 'index']
        );

        $routes->connect(
            Configure::read('Swagger.docs.route') . ':id',
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Docs', 'action' => 'index']
        )
            ->setPatterns(['id' => '\w+'])
            ->setPass(['id']);
    } else {
        $routes->connect(
            '/alt3/swagger/docs',
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Docs', 'action' => 'index']
        );

        $routes->connect(
            '/alt3/swagger/docs/:id',
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Docs', 'action' => 'index']
        )
            ->setPatterns(['id' => '\w+'])
            ->setPass(['id']);
    }
});
