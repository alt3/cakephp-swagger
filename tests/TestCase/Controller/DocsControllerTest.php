<?php
declare(strict_types=1);

namespace Alt3\Swagger\Test\TestCase\Controller;

use Alt3\Swagger\Controller\DocsController;
use Alt3\Swagger\Test\App\Application;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use StdClass;

class DocsControllerTest extends TestCase
{
    /**
     * @var \Alt3\Swagger\Controller\DocsController
     */
    protected $controller;

    /**
     * @var array Default AppController settings every test will start with.
     */
    protected static $defaultConfig = [
        'docs' => [
            'crawl' => true,
        ],
        'ui' => [
            'title' => 'cakephp-swagger',
        ],
    ];

    /**
     * @var array Default CakePHP API success response structure.
     */
    protected static $apiResponseBody = [
        'success' => true,
        'data' => [],
    ];

    /**
     * setUp method executed before every testMethod.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->controller = new DocsController();

        $app = new Application(CONFIG);
        $app->bootstrap();
        $builder = Router::createRouteBuilder('/');
        $app->routes($builder);
        $app->pluginRoutes($builder);
    }

    /**
     * tearDown method executed after every testMethod.
     */
    public function tearDown(): void
    {
        parent::tearDownAfterClass();
        $testDoc = CACHE . 'cakephp_swagger_testdoc.json';
        if (file_exists($testDoc)) {
            unlink($testDoc);
        }
    }

    /**
     * Make sure our tests are using the expected configuration settings.
     */
    public function testDefaultSettings()
    {
        $this->assertSame(self::$defaultConfig, DocsController::$defaultConfig);
        $this->assertSame(self::$apiResponseBody, DocsController::$apiResponseBody);
    }

    /**
     * Make sure the function responsible for generating the json list with
     * documents (index action, no argument) returns the expected json.
     */
    public function testMethodGetJsonDocumentList()
    {
        $reflection = self::getReflection($this->controller);

        // no library should return empty json success response
        $result = $reflection->methods->getJsonDocumentList->invokeArgs($this->controller, []);
        $expected = json_encode(self::$apiResponseBody, JSON_PRETTY_PRINT);
        $this->assertSame($expected, $result);

        // no documents in library should return empty json success response
        $reflection->properties->config->setValue($this->controller, array_merge(self::$defaultConfig, [
            'library' => [],
        ]));
        $result = $reflection->methods->getJsonDocumentList->invokeArgs($this->controller, []);
        $expected = json_encode(self::$apiResponseBody, JSON_PRETTY_PRINT);
        $this->assertSame($expected, $result);

        // filled library should return json data body with links
        $reflection->properties->config->setValue($this->controller, array_merge(self::$defaultConfig, [
            'library' => [
                'testdoc1' => [],
                'testdoc2' => [],
            ],
        ]));

        $expected = <<<'EOF'
{
    "success": true,
    "data": [
        {
            "document": "testdoc1",
            "link": "http://localhost/alt3/swagger/docs/testdoc1"
        },
        {
            "document": "testdoc2",
            "link": "http://localhost/alt3/swagger/docs/testdoc2"
        }
    ]
}
EOF;
        $result = $reflection->methods->getJsonDocumentList->invokeArgs($this->controller, []);
        $this->assertSame($expected, $result);

        // index action should return null when no document id is passed
        $result = $reflection->methods->index->invokeArgs($this->controller, []);
        $this->assertSame(null, $result);
    }

    /**
     * Make sure missing that a missing library section in the configuration
     * file will throw an exception when a document is being requested.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Swagger configuration file does not contain a library section
     */
    public function testMissingLibrarySection()
    {
        $reflection = self::getReflection($this->controller);
        $reflection->methods->index->invokeArgs($this->controller, ['testdoc']);
    }

    /**
     * Make sure that an empty library in the configuration file will throw
     * an exception when a document is being requested.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp #Swagger configuration file does not contain a document definition for *#
     */
    public function testEmptyLibrary()
    {
        $reflection = self::getReflection($this->controller);
        $reflection->properties->config->setValue($this->controller, array_merge(self::$defaultConfig, [
            'library' => [],
        ]));
        $reflection->methods->index->invokeArgs($this->controller, ['testdoc']);
    }

    /**
     * Make sure missing or empty CORS headers definition in the configuration
     * file will not trigger adding headers to the json response.
     *
     * @return void
     */
    public function testAddingCorsHeaders()
    {
        $reflection = self::getReflection($this->controller);

        // cors headers not in configuration
        $result = $reflection->methods->addCorsHeaders->invokeArgs($this->controller, []);
        $this->assertFalse($result);

        // cors headers section in configuration but no entries
        $reflection->properties->config->setValue($this->controller, array_merge(self::$defaultConfig, [
            'docs' => [
                'cors' => [],
            ],
        ]));
        $result = $reflection->methods->addCorsHeaders->invokeArgs($this->controller, []);
        $this->assertFalse($result);
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
