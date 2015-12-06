<?php
namespace Alt3\Swagger\Controller;

use Cake\Core\Configure;
use Cake\Core\Exception;
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
        $this->viewBuilder()->layout(false);

        $this->set('config', static::$config['ui']);

        // Load petstore document if library contains no entries
        if (empty(static::$config['library'])) {
            $this->set('url', 'http://petstore.swagger.io/v2/swagger.json');
            return;
        }

        // Otherwise load first document found in the library
        $defaultDocument = key(static::$config['library']);
        $this->set('url', Router::url([
            'plugin' => 'Alt3/Swagger',
            'controller' => 'Docs',
            'action' => 'index',
            $defaultDocument
        ], true));
    }
}
