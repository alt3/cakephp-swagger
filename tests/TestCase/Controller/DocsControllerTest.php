<?php
namespace Alt3\Swagger\Test\TestCase\Controller;

use Alt3\Swagger\Controller\DocsController;
use Cake\Core\Configure;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\TestSuite\TestCase;
use StdClass;

class DocsControllerTest extends TestCase
{
    public $controller = null;

    /**
     * Test calling docs page without :id parameter
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Missing cakephp-swagger library argument
     **/
    public function testMissingIndexArgument()
    {
        $controller = new DocsController();
        $reflection = $reflection = $this->getReflection($controller, 'index');
        $result = $reflection->method->invokeArgs($controller, [null]);
    }

    /**
     * Test missing library section in configuration file
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage cakephp-swagger configuration misses library section
     **/
    public function testMissingLibrarySection()
    {
        $controller = new DocsController();
        $reflection = $reflection = $this->getReflection($controller, 'index', 'config');
        $result = $reflection->method->invokeArgs($controller, ['api']);
    }

    /**
     * Test missing document definition in configuration file
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage cakephp-swagger configuration misses document definition for 'non-existing-document'
     **/
    public function testMissingDocumentSection()
    {
        $controller = new DocsController();
        $reflection = $reflection = $this->getReflection($controller, 'index', 'config');

        // extend AppController config with expected settings
        $config = $reflection->property->getValue($controller);
        $reflection->property->setValue($controller, array_merge($config, [
            'library' => []
        ]));

        $result = $reflection->method->invokeArgs($controller, ['non-existing-document']);
    }

    /**
     * Test document not found in cache (in non-development mode)
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage cakephp-swagger could not load document from cache
     **/
    public function testDocumentNotInCache()
    {
        $controller = new DocsController();
        $reflection = $reflection = $this->getReflection($controller, 'index', 'config');
        $config = $reflection->property->getValue($controller);

        $randomDocument = rand(0, 10000000);
        $reflection->property->setValue($controller, array_merge($config, [
            'noCache' => false, // enable caching
            'library' => [
                $randomDocument => []
            ]
        ]));
        $result = $reflection->method->invokeArgs($controller, [$randomDocument]);
    }

    /**
     * Test swagger source crawling
     *
     * @return void
     **/
    public function testSourceCrawling()
    {
        $controller = new DocsController();
        $reflection = $reflection = $this->getReflection($controller, 'getSwaggerDocument', 'config');
        $config = $reflection->property->getValue($controller);
        $reflection->property->setValue($controller, array_merge($config, [
            'noCache' => true,
            'library' => [
                'api' => [
                    'include' => [
                        'include' => APP . 'src',
                    ]
                ]
            ]
        ]));
        $result = $reflection->method->invokeArgs($controller, ['api']);
        //pr($reflection->property->getValue($controller));
    }


    /**
     * Test setting CORS headers
     *
     * @return void
     **/
    public function testSettingCorsHeaders()
    {
        $controller = new DocsController();
        $reflection = $reflection = $this->getReflection($controller, 'setCorsHeaders', 'config');
        $result = $reflection->method->invokeArgs($controller, []);
        $this->assertFalse($result);

        // CORS array present in configuration but empty
        $config = $reflection->property->getValue($controller);
        $reflection->property->setValue($controller, array_merge($config, [
            'docs' => [
                'cors' => []
            ]
        ]));
        $result = $reflection->method->invokeArgs($controller, []);
        $this->assertFalse($result);
    }



    /**
     * Convenience function to return an object with reflection class, accessible
     * protected method and optional accessible protected property.
     */
    public function getReflection($object, $method = false, $property = false)
    {
        $obj = new stdClass();
        $obj->class = new \ReflectionClass(get_class($object));
        $obj->method = null;
        if ($method) {
            $obj->method = $obj->class->getMethod($method);
            $obj->method->setAccessible(true);
        }
        if ($property) {
            $obj->property = $obj->class->getProperty($property);
            $obj->property->setAccessible(true);
        }
        return $obj;
    }
}
