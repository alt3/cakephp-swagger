<?php
namespace Alt3\Swagger\Controller;

use Cake\Cache\Cache;
use Cake\Core\Configure;

class DocsController extends AppController
{

    /**
     * Index action.
     *
     * @param string $id Name of swagger document to generate/serve
     * @throws \InvalidArgumentException
     * @return void
     */
    public function index($id)
    {
        if (!$id) {
            throw new \InvalidArgumentException("Missing cakephp-swagger library argument");
        }

        if (!isset(static::$config['library'])) {
            throw new \InvalidArgumentException("cakephp-swagger configuration misses library section");
        }

        if (!array_key_exists($id, static::$config['library'])) {
            throw new \InvalidArgumentException("cakephp-swagger configuration misses document definition for '$id'");
        }

        $this->set('swaggerString', $this->getSwaggerDocument($id));
        $this->viewBuilder()->layout(false);
        $this->addCorsHeaders();
        $this->response->type('json');
    }

    /**
     * Return a swagger document from cache or by crawling.
     *
     * @param string $id Name of the document
     * @throws \InvalidArgumentException
     * @return string
     */
    protected function getSwaggerDocument($id)
    {
        $documentCacheKey = $this->cachePrefix . $id;

        // either try loading document from cache
        if (static::$config['noCache'] === false) {
            $swagger = Cache::read($documentCacheKey);
            if ($swagger === false) {
                throw new \InvalidArgumentException("cakephp-swagger could not load document from cache");
            }
            return $swagger;
        }

        // or crawl-generate a fresh document
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

        Cache::write($documentCacheKey, $swagger);
        return $swagger;
    }

    /**
     * Set CORS headers if found in configuration
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
