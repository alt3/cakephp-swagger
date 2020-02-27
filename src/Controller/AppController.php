<?php
declare(strict_types=1);

namespace Alt3\Swagger\Controller;

use Cake\Controller\Controller as BaseController;
use Cake\Core\Configure;
use Cake\Utility\Hash;

class AppController extends BaseController
{
    /**
     * @var array that will hold merged configuration settings.
     */
    public $config;

    /**
     * @var array holding required default configuration settings.
     */
    public static $defaultConfig = [
        'docs' => [
            'crawl' => true,
        ],
        'ui' => [
            'title' => 'cakephp-swagger',
        ],
    ];

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        if (Configure::read('Swagger')) {
            $this->config = Hash::merge(static::$defaultConfig, Configure::read('Swagger'));
        }
    }
}
