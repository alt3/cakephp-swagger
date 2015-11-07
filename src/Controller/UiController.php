<?php
namespace Alt3\Swagger\Controller;

use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Core\Exception;
use Cake\Routing\Router;

/**
 * UiController class responsible for serving the swagger-ui template page.
 *
 * @package Alt3\Swagger\Controller
 */
class UiController extends Controller
{

    /**
     * Index action used for setting template variables.
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
