<?php
namespace Alt3\Swagger\Controller;

use Cake\Controller\Controller as BaseController;
use Cake\Core\Configure;
use Cake\Core\Exception\Exception;
use Cake\Core\Plugin;

class AppController extends BaseController
{

    /**
     * @var string
     */
    protected $configFile = 'swagger';

    /**
     * @var string
     */
    protected $cachePrefix = '_cakephp_swagger_';

    /**
     * @var array
     */
    public static $config = [
        'noCache' => true,
        'definitions' => [],
        'cors_headers' => [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST',
            'Access-Control-Allow-Headers' => 'X-Requested-With'
        ],
        'ui' => [
            'page_title' => 'cakephp-swagger'
        ]
    ];

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        $configPath = CONFIG . $this->configFile . '.php';
        if (!file_exists($configPath)) {
            throw new Exception("cakephp-swagger configuration file does not exist: $configPath");
        }
        Configure::load($this->configFile, 'default');
        static::$config = array_merge(static::$config, Configure::read('Swagger'));
    }
}
