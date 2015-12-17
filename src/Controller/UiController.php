<?php
namespace Alt3\Swagger\Controller;

use Cake\Core\Configure;
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
        $this->set('uiConfig', static::$config['ui']);
        $this->set('url', $this->getDefaultDocumentUrl());
    }

    /**
     * @return string
     */
    public function getDefaultDocumentUrl()
    {
        // Use Swagger petstore if library contains no entries
        if (empty(static::$config['library'])) {
            return ('http://petstore.swagger.io/v2/swagger.json');
        }

        // Otherwise generate URL using first document in the library
        $defaultDocument = key(static::$config['library']);
        return (Router::url([
            'plugin' => 'Alt3/Swagger',
            'controller' => 'Docs',
            'action' => 'index',
            $defaultDocument
        ], true));
    }
}
