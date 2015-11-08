<?php
namespace Alt3\Swagger\Controller;

use Cake\Controller\Controller as BaseController;
use Cake\Core\Configure;

class AppController extends BaseController
{

    /**
     * @var string
     */
    protected $configFile = 'swagger';

    /**
     * @var array
     */
    public static $config = [
        'include_path' => ROOT . DS . 'src',
        'exclude_paths' => [],
        'cors' => [
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

        if (!file_exists(CONFIG . $this->configFile . '.php')) {
            return;
        }
        Configure::load($this->configFile, 'default');
        static::$config = array_merge(static::$config, Configure::read('Swagger'));
    }
}
