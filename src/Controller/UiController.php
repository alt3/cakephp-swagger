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
        $this->viewBuilder()->layout(false);

        $this->set('config', static::$config['ui']);

        // make the first document autoload inside the UI
        $defaultDocument = key(static::$config['documents']);
        if (!$defaultDocument) {
            throw new \InvalidArgumentException("cakephp-swagger configuration file does not contain any documents");
        }

        $this->set('url', Router::url([
            'plugin' => 'Alt3/Swagger',
            'controller' => 'Docs',
            'action' => 'index',
            $defaultDocument
        ], true));
    }
}
