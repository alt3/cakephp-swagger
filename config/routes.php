<?php
use Cake\Routing\Router;

Router::plugin('Alt3/Swagger', function ($routes) {
    $routes->fallbacks('DashedRoute');
});
