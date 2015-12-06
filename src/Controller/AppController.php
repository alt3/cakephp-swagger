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
     * @var string
     */
    protected $cachePrefix = '_cakephp_swagger_';

    /**
     * @var array
     */
    public static $config = [
        'noCache' => true,
        'documents' => [],
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
        if (Configure::read('Swagger')) {
            static::$config = array_merge(static::$config, Configure::read('Swagger'));
        }
    }
}
