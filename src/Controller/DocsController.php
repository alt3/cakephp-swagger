<?php
namespace Alt3\Swagger\Controller;

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\Network\Exception\InternalErrorException;
use Cake\Network\Exception\NotFoundException;

class DocsController extends AppController
{

    /**
     * @var string Prepended to filesystem swagger json files
     */
    protected $filePrefix = 'cakephp_swagger_';

    /**
     * Index action.
     *
     * @param string $id Name of swagger document to generate/serve
     * @throws \InvalidArgumentException
     * @return void
     */
    public function index($id = null)
    {
        if (!$id) {
            throw new \InvalidArgumentException('Missing required argument holding Swagger document name');
        }

        if (!isset(static::$config['library'])) {
            throw new \InvalidArgumentException('Swagger configuration file does not contain a library section');
        }

        if (!array_key_exists($id, static::$config['library'])) {
            throw new \InvalidArgumentException("Swagger configuration file does not contain a document definition for '$id'");
        }

        $this->set('swaggerString', $this->getSwaggerDocument($id));
        $this->viewBuilder()->layout(false);
        $this->addCorsHeaders();
        $this->response->type('json');
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
        if (!static::$config['docs']['crawl']) {
            if (!file_exists($filePath)) {
                throw new NotFoundException("Swagger json document was not found on filesystem: $filePath");
            }
            $fh = new File($filePath);

            return $fh->read();
        }

        // otherwise crawl-generate a fresh document
        $swaggerOptions = null;
        if (isset(static::$config['library'][$id]['exclude'])) {
            $swaggerOptions = [
                'exclude' => static::$config['library'][$id]['exclude']
            ];
        }
        $swagger = \Swagger\scan(static::$config['library'][$id]['include'], $swaggerOptions);

        // set properties required by UI to generate the BASE URL
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
        if (!isset(static::$config['docs']['cors'])) {
            return false;
        }

        if (!count(static::$config['docs']['cors'])) {
            return false;
        }

        foreach (static::$config['docs']['cors'] as $header => $value) {
            $this->response->header($header, $value);
        }
    }
}
