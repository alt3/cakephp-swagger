<?php
namespace Alt3\Swagger\Test\TestCase\Controller;

use Alt3\Swagger\Controller\UiController;
use Cake\TestSuite\TestCase;
use StdClass;

class UiControllerTest extends TestCase
{
    /**
     * Make sure the UI will auto-load the correct default document URL.
     *
     * @return void
     **/
    public function testMethodGetDefaultDocumentUrl()
    {
        // test without library document
        $controller = new UiController();
        $reflection = $reflection = $this->getReflection($controller, 'getDefaultDocumentUrl', 'config');
        $reflection->property->setValue([
            'docs' => []
        ]);
        $result = $reflection->method->invokeArgs($controller, []);
        $this->assertTextEquals($result, 'http://petstore.swagger.io/v2/swagger.json');

        // test with library document
        $reflection->property->setValue([
            'library' => [
                'testdoc' => []
            ]
        ]);
        $result = $reflection->method->invokeArgs($controller, []);
        $this->assertTextEquals($result, 'http://localhost/alt3/swagger/docs/testdoc');
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
