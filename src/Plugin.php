<?php

namespace Alt3\Swagger;

use Cake\Core\BasePlugin;
use Cake\Core\Configure;
use Cake\Core\PluginApplicationInterface;

/**
 * Class Plugin
 * @package Alt3\Swagger
 */
class Plugin extends BasePlugin
{
    /**
     * Load all the application configuration and bootstrap logic.
     *
     * The default implementation of this method will include the `config/bootstrap.php` in the plugin if it exist. You
     * can override this method to replace that behavior.
     *
     * The host application is provided as an argument. This allows you to load additional
     * plugin dependencies, or attach events.
     *
     * @param \Cake\Core\PluginApplicationInterface $app The host application
     * @return void
     */
    public function bootstrap(PluginApplicationInterface $app)
    {
        $config = 'swagger';
        $configPath = CONFIG . $config . '.php';
        if (file_exists($configPath)) {
            Configure::load($config, 'default');
        }

        parent::bootstrap($app);
    }
}
