<?php
namespace Alt3\Swagger\Controller;

use Alt3\Swagger\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Exception;
use Cake\Network\Exception\NotFoundException;
use Cake\Routing\Router;

/**
 * UiController class responsible for serving the swagger-ui template page.
 *
 * @package Alt3\Swagger\Controller
 */
class UiController extends AppController
{

    /**
     * Index action used for setting template variables.
     *
     * @return void
     */
    public function index()
    {
        $this->set('config', static::$config['ui']);

        // make the first definition autoload inside the UI
        $defaultDefinition = key(static::$config['definitions']);
        if (!$defaultDefinition) {
            throw new \InvalidArgumentException("cakephp-swagger configuration file does not contain any definitions");
        }

        $this->set('url', Router::url([
            'plugin' => 'Alt3/Swagger',
            'controller' => 'Definitions',
            'action' => 'index',
            $defaultDefinition
        ], true));
    }
}
