<?php
namespace Alt3\Swagger\Controller;

use Alt3\Swagger\Lib\SwaggerTools;
use Cake\Routing\Router;

class DocsController extends AppController
{

    /**
     * @var array Default CakePHP API success response structure.
     */
    public static $apiResponseBody = [
        'success' => true,
        'data' => []
    ];

    /**
     * Index action used to produce a JSON response containing either a swagger
     * document (if a valid id argument is passed) or a list with links to all
     * aavailable documents (if defined in the library).
     *
     * @param string $id Name of swagger document to generate/serve
     * @throws \InvalidArgumentException
     * @return void
     */
    public function index($id = null)
    {
        if (!$id) {
            $this->jsonResponse($this->getJsonDocumentList());

            return;
        }

        if (!isset($this->config['library'])) {
            throw new \InvalidArgumentException('Swagger configuration file does not contain a library section');
        }

        if (!array_key_exists($id, $this->config['library'])) {
            throw new \InvalidArgumentException("Swagger configuration file does not contain a document definition for '$id'");
        }

        $document = SwaggerTools::getSwaggerDocument($id, $this->request->host());
        $this->jsonResponse($document);
    }

    /**
     * Creates a json string containing fullBase links to all documents in the
     * library (useful for e.g. displaying on the /docs index action).
     *
     * @return string
     */
    protected function getJsonDocumentList()
    {
        if (!isset($this->config['library'])) {
            return json_encode(static::$apiResponseBody, JSON_PRETTY_PRINT);
        }

        if (!count($this->config['library'])) {
            return json_encode(static::$apiResponseBody, JSON_PRETTY_PRINT);
        }

        foreach (array_keys($this->config['library']) as $document) {
            static::$apiResponseBody['data'][] = [
                'document' => $document,
                'link' => Router::url([
                    'plugin' => 'Alt3/Swagger',
                    'controller' => 'Docs',
                    'action' => 'index',
                    $document
                ], true)
            ];
        }

        return json_encode(static::$apiResponseBody, JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES);
    }

    /**
     * Set CORS headers if found in configuration.
     *
     * @return bool|void
     */
    protected function addCorsHeaders()
    {
        // set CORS headers if specified in config
        if (!isset($this->config['docs']['cors'])) {
            return false;
        }

        if (!count($this->config['docs']['cors'])) {
            return false;
        }

        foreach ($this->config['docs']['cors'] as $header => $value) {
            $this->response->header($header, $value);
        }
    }

    /**
     * Configures the json response before calling the index view.
     *
     * @param string $json JSON encoded string
     * @return void
     */
    protected function jsonResponse($json)
    {
        $this->set('json', $json);
        $this->viewBuilder()->layout(false);
        $this->addCorsHeaders();
        $this->response->type('json');
    }
}
