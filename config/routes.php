<?php
use Cake\Routing\Router;

Router::plugin('Alt3/Swagger', function ($routes) {

    // Make index support url arguments so we can support multiple definitions
    Router::connect(
        '/alt3/swagger/definitions/:id',
        ['plugin' => 'Alt3/Swagger', 'controller' => 'Definitions', 'action' => 'index'],
        ['id' => '\w+', 'pass' => ['id']]
    );

    $routes->fallbacks('DashedRoute');
});
