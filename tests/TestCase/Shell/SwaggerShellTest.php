<?php
namespace Alt3\Swagger\Test\TestCase\Shell;

use Alt3\Swagger\Shell\SwaggerShell;
use Cake\TestSuite\TestCase;
use StdClass;

class SwaggerShellTest extends TestCase
{

    /**
     * @var \Alt3\Swagger\Shell\SwaggerShell SwaggerShell instance.
     */
    protected $shell;

    /**
     * setUp method.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->io = $this->createMock('Cake\Console\ConsoleIo');
        $this->shell = new SwaggerShell($this->io);
    }

    /**
     * tearDown method.
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->shell);
        parent::tearDown();
    }

    /**
     * Test the parser.
     */
    public function testParser()
    {
        $reflection = self::getReflection($this->shell);
        $result = $reflection->methods->getOptionParser->invokeArgs($this->shell, []);
        $this->assertInstanceOf('\Cake\Console\ConsoleOptionParser', $result);
    }

    /**
     * Test the makedocs command.
     *
     * @return bool
     */
    public function testMakeDocs()
    {
        $reflection = self::getReflection($this->shell);
        $result = $reflection->methods->makedocs->invokeArgs($this->shell, ['www.test.api']);
        $this->assertSame(null, $result);
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
