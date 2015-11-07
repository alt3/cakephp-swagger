<?php
namespace Alt3\Swagger\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Exception;
use Cake\Routing\Router;

/**
 * Class UiController responsible for the swagger-ui page
 *
 * @package Alt3\Swagger\Controller
 */
class UiController extends Controller
{

    /**
     * Index action
     *
     * @return void
     */
    public function index()
    {
        $this->set('url', Router::url([
            'plugin' => 'Alt3/Swagger',
            'controller' => 'Definitions'
        ], true));
    }
}
