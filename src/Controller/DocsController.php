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
    public function index($id = null)
    {
        if (!$id) {
            throw new \InvalidArgumentException("cakephp-swagger document requires an argument");
        }

        if (!array_key_exists($id, static::$config['library'])) {
            throw new \InvalidArgumentException("cakephp-swagger configuration file does not contain a document for '$id'");
        }

        // load document from cache
        $cacheKey = $this->cachePrefix . $id;
        if (static::$config['noCache'] === false) {
            $swagger = Cache::read($cacheKey);
            if ($swagger === false) {
                throw new \InvalidArgumentException("cakephp-swagger could not load document from cache using key $cacheKey");
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
            Cache::delete($cacheKey);
            Cache::write($cacheKey, $swagger);
        }

        // set CORS headers unless disabled in config
        if (count(static::$config['cors_headers'])) {
            foreach (static::$config['cors_headers'] as $header => $value) {
                header("$header: $value");
            }
        }

        // Serve swagger document in memory as json
        header('Content-Type: application/json');
        echo $swagger;
        exit(0);
    }
}
