<?php
namespace Alt3\Swagger\Controller;

use Alt3\Swagger\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Network\Exception\NotFoundException;

class DefinitionsController extends AppController
{

    /**
     * Index action.
     *
     * @param string $id Name of definition to generate/serve
     * @return void
     */
    public function index($id = null)
    {
        if (!$id) {
            throw new \InvalidArgumentException("cakephp-swagger requires a definition argument");
        }

        if (!array_key_exists($id, static::$config['definitions'])) {
            throw new NotFoundException("cakephp-swagger configuration file does not contain a definition for '$id'");
        }

        // load definition from cache
        $cacheKey = $this->cachePrefix . $id;
        if (static::$config['noCache'] === false) {
            $swagger = Cache::read($cacheKey);
            if ($swagger === false) {
                throw new \InvalidArgumentException("cakephp-swagger could not load definition from cache using key $cacheKey");
            }
        }

        // generate new definition
        if (static::$config['noCache'] === true) {
            $swagger = \Swagger\scan(static::$config['definitions'][$id]['include'], [
                'exclude' => static::$config['definitions'][$id]['exclude']
            ]);
            Cache::delete($cacheKey);
            Cache::write($cacheKey, $swagger);
        }

        // set CORS headers unless disabled in config
        if (count(static::$config['cors_headers'])) {
            foreach (static::$config['cors_headers'] as $header => $value) {
                header("$header: $value");
            }
        }

        // Serve swagger definition in memory as json
        header('Content-Type: application/json');
        echo $swagger;
        exit(0);
    }
}
