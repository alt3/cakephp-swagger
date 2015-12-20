<?php
namespace Alt3\Swagger\Controller;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\NotFoundException;
use Cake\Routing\Router;

class DocsController extends AppController
{

    /**
     * @var string Prepended to filesystem swagger json files
     */
    protected $filePrefix = 'cakephp_swagger_';

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

        $this->jsonResponse($this->getSwaggerDocument($id));
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
     * Returns a swagger document from filesystem or crawl-generates a fresh one.
     *
     * @param string $id Name of the document
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function getSwaggerDocument($id)
    {
        // load document from filesystem
        $filePath = CACHE . $this->filePrefix . $id . '.json';
        if (!$this->config['docs']['crawl']) {
            if (!file_exists($filePath)) {
                throw new NotFoundException("Swagger json document was not found on filesystem: $filePath");
            }
            $fh = new File($filePath);

            return $fh->read();
        }

        // otherwise crawl-generate a fresh document
        $swaggerOptions = null;
        if (isset($this->config['library'][$id]['exclude'])) {
            $swaggerOptions = [
                'exclude' => $this->config['library'][$id]['exclude']
            ];
        }
        $swagger = \Swagger\scan($this->config['library'][$id]['include'], $swaggerOptions);

        // set object properties required by UI to generate the BASE URL
        $swagger->host = $this->request->host();
        $swagger->basePath = '/' . Configure::read('App.base');
        $swagger->schemes = Configure::read('Swagger.ui.schemes');

        // write document to filesystem
        $this->writeSwaggerDocumentToFile($filePath, $swagger);

        return $swagger;
    }

    /**
     * Write swagger document to filesystem.
     *
     * @param string $path Full path to the json document including filename
     * @param string $content Swagger content
     * @throws Cake\Network\Exception\InternalErrorException
     * @return bool
     */
    protected function writeSwaggerDocumentToFile($path, $content)
    {
        $fh = new File($path, true);
        if (!$fh->write($content)) {
            throw new InternalErrorException('Error writing Swagger json document to filesystem');
        }

        return true;
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
