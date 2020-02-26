<?php
namespace Alt3\Swagger\Test\App;

use Alt3\Swagger\Plugin;
use Cake\Http\BaseApplication;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Http\MiddlewareQueue;

class Application extends BaseApplication
{
    /**
     * Loads the assets and routing middleware which are necessary to test the plugin.
     *
     * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to set in your App Class
     * @return \Cake\Http\MiddlewareQueue
     */
    public function middleware($middlewareQueue): MiddlewareQueue
    {
        $middlewareQueue
            // Handle plugin/theme assets like CakePHP normally does.
            ->add(AssetMiddleware::class)
            // Add routing middleware.
            ->add(new RoutingMiddleware($this, null));

        return $middlewareQueue;
    }

    public function bootstrap(): void
    {
        $this->addPlugin(Plugin::class, ['routes' => true, 'bootstrap' => true]);
        parent::bootstrap();
    }
}
