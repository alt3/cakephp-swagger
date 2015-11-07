<?php
namespace Alt3\Swagger\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Exception;
use Swagger;

class DefinitionsController extends Controller
{
    /**
     * @var name of the swagger file (used in production mode)
     */
    protected $swaggerFile = 'swagger.json';

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
        $swagger = \Swagger\scan(ROOT . DS . 'src', [
            'exclude' => [
                '/Editor/'
            ]
        ]);
        echo $swagger;
        exit(0);
    }
}
