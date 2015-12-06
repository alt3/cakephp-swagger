<?php

use Cake\Core\Configure;
use Cake\Routing\Router;

/**
 * Load app-specific configuration file
 */
$configFile = 'swagger';
$configPath = CONFIG . $configFile . '.php';
if (!file_exists($configPath)) {
    throw new Exception("cakephp-swagger configuration file does not exist: $configPath");
}
Configure::load($configFile, 'default');

/**
 * Connect routes:
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
            Configure::read('Swagger.docs.route') . ':id',
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Docs', 'action' => 'index'],
            ['id' => '\w+', 'pass' => ['id']]
        );
    } else {
        Router::connect(
            '/alt3/swagger/docs/:id',
            ['plugin' => 'Alt3/Swagger', 'controller' => 'Docs', 'action' => 'index'],
            ['id' => '\w+', 'pass' => ['id']]
        );
    }
});
