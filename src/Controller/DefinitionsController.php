<?php
namespace Alt3\Swagger\Controller;

use Alt3\Swagger\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Exception;
use Swagger;

class DefinitionsController extends AppController
{

    /**
     * Index action.
     *
     * @return void
     */
    public function index()
    {
        // respond with json
        header('Content-Type: application/json');

        // debug mode: crawl directory in real-time
        $swagger = \Swagger\scan(static::$config['include_path'], [
            'exclude' => static::$config['exclude_paths']
        ]);
        echo $swagger;
        exit(0);
    }
}
