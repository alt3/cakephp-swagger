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

        $swagger = $this->getSwaggerDocument($id);
        $this->setCorsHeaders();

        // Serve swagger document in memory as json
        header('Content-Type: application/json');
        echo $swagger;
        exit(0);
    }

    /**
     * Return a swagger document from cache or by crawling.
     *
     * @param string $id Name of the document
     * @return string
     */
    protected function getSwaggerDocument($id)
    {
        // load document from cache
        $documentCacheKey = $this->cachePrefix . $id;
        if (static::$config['noCache'] === false) {
            $swagger = Cache::read($documentCacheKey);
            if ($swagger === false) {
                throw new \InvalidArgumentException("cakephp-swagger could not load document from cache");
            }
        }

        // generate new document
        if (static::$config['noCache'] === true) {
            $swaggerOptions = null;
            if (isset(static::$config['library'][$id]['exclude'])) {
                $swaggerOptions = [
                    'exclude' => static::$config['library'][$id]['exclude']
                ];
            }

            $swagger = \Swagger\scan(static::$config['library'][$id]['include'], $swaggerOptions);
            Cache::delete($documentCacheKey);
            Cache::write($documentCacheKey, $swagger);
        }
        return $swagger;
    }

    /**
     * Set CORS headers if found in configuration
     *
     * @return bool|void
     */
    protected function setCorsHeaders()
    {
        // set CORS headers if specified in config
        if (!isset(static::$config['docs']['cors'])) {
            return false;
        }

        if (!count(static::$config['docs']['cors'])) {
            return false;
        }

        foreach (static::$config['docs']['cors'] as $header => $value) {
            header("$header: $value");
        }
    }
}
