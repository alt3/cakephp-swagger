<?php
/**
 * test_app routes.php. Remove configuration settings from any previous tests
 * before loading plugin routes.php file.
 */
use Cake\Core\Configure;

Configure::delete('Swagger');
require ROOT . 'config/routes.php';
