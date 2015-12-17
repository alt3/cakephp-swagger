<?php
namespace Alt3\Swagger\Controller;

use Cake\Controller\Controller as BaseController;
use Cake\Core\Configure;
use Cake\Utility\Hash;

class AppController extends BaseController
{

    /**
     * @var string
     */
    protected $configFile = 'swagger';

    /**
     * @var string
     */
    protected $filePrefix = 'cakephp_swagger_';

    /**
     * @var array default configuration settings.
     */
    public static $defaultConfig = [
        'docs' => [
            'crawl' => true
        ],
        'ui' => [
            'title' => 'cakephp-swagger'
        ]
    ];

    /**
     * @var array holding merged configuration.
     */
    public static $config;

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize()
    {
        parent::initialize();

        if (Configure::read('Swagger')) {
            static::$config = Hash::merge(static::$defaultConfig, Configure::read('Swagger'));
        }
    }
}
