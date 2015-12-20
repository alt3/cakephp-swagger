<?php
namespace Alt3\Swagger\Test\TestCase\Controller;

use Alt3\Swagger\Controller\UiController;
use Cake\TestSuite\TestCase;
use StdClass;

class UiControllerTest extends TestCase
{

    /**
     * @var \Alt3\Swagger\Controller\UiController
     */
    protected $controller;

    /**
     * @var array Default AppController settings every test will start with.
     */
    protected static $defaultConfig = [
        'docs' => [
            'crawl' => true
        ],
        'ui' => [
            'title' => 'cakephp-swagger'
        ]
    ];

    /**
     * setUp method executed before every testMethod.
     */
    public function setUp()
    {
        parent::setUp();
        $this->controller = new UiController();
    }

    /**
     * Make sure our tests are using the expected configuration settings.
     */
    public function testDefaultSettings()
    {
        $this->assertSame(self::$defaultConfig, UiController::$defaultConfig);
    }

    /**
     * Make sure the UI will auto-load the correct default document URL.
     *
     * @return void
     **/
    public function testMethodGetDefaultDocumentUrl()
    {
        $reflection = self::getReflection($this->controller);

        // test without library documents
        $reflection->properties->config->setValue($this->controller, array_merge(self::$defaultConfig, [
            'library' => []
        ]));
        $result = $reflection->methods->getDefaultDocumentUrl->invokeArgs($this->controller, []);
        $this->assertSame($result, 'http://petstore.swagger.io/v2/swagger.json');

        // test with library document
        $reflection->properties->config->setValue($this->controller, [
            'library' => [
                'testdoc' => []
            ]
        ]);
        $result = $reflection->methods->getDefaultDocumentUrl->invokeArgs($this->controller, []);
        $this->assertSame($result, 'http://localhost/alt3/swagger/docs/testdoc');
    }

    /**
     * Convenience function to return an object with reflection class,
     * accessible protected methods and accessible protected properties.
     */
    protected static function getReflection($object)
    {
        $obj = new stdClass();
        $obj->class = new \ReflectionClass(get_class($object));

        // make all methods accessible
        $obj->methods = new stdClass();
        $classMethods = $obj->class->getMethods();
        foreach ($classMethods as $method) {
            $methodName = $method->name;
            $obj->methods->{$methodName} = $obj->class->getMethod($methodName);
            $obj->methods->{$methodName}->setAccessible(true);
        }

        // make all properties accessible
        $obj->properties = new stdClass();
        $classProperties = $obj->class->getProperties();
        foreach ($classProperties as $property) {
            $propertyName = $property->name;
            $obj->properties->{$propertyName} = $obj->class->getProperty($propertyName);
            $obj->properties->{$propertyName}->setAccessible(true);
        }
        return $obj;
    }
}
