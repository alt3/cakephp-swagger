<?php
use Cake\Routing\Router;

Router::plugin('Alt3/Swagger', function ($routes) {

    // Make index support url arguments so we can support multiple swagger documents
    Router::connect(
        '/alt3/swagger/docs/:id',
        ['plugin' => 'Alt3/Swagger', 'controller' => 'Docs', 'action' => 'index'],
        ['id' => '\w+', 'pass' => ['id']]
    );

    $routes->fallbacks('DashedRoute');
});
