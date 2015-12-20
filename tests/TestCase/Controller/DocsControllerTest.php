<?php
namespace Alt3\Swagger\Test\TestCase\Controller;

use Alt3\Swagger\Controller\DocsController;
use Cake\Cache\Cache;
use Cake\Filesystem\File;
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
            'crawl' => true
        ],
        'ui' => [
            'title' => 'cakephp-swagger'
        ]
    ];

    /**
     * @var array Default CakePHP API success response structure.
     */
    protected static $apiResponseBody = [
        'success' => true,
        'data' => []
    ];

    /**
     * setUp method executed before every testMethod.
     */
    public function setUp()
    {
        parent::setUp();
        $this->controller = new DocsController();
    }

    /**
     * tearDown method executed after every testMethod.
     */
    public function tearDown()
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
            'library' => []
        ]));
        $result = $reflection->methods->getJsonDocumentList->invokeArgs($this->controller, []);
        $expected = json_encode(self::$apiResponseBody, JSON_PRETTY_PRINT);
        $this->assertSame($expected, $result);

        // filled library should return json data body with links
        $reflection->properties->config->setValue($this->controller, array_merge(self::$defaultConfig, [
            'library' => [
                'testdoc1' => [],
                'testdoc2' => []
            ]
        ]));

        $expected = <<<EOF
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
            'library' => []
        ]));
        $reflection->methods->index->invokeArgs($this->controller, ['testdoc']);
    }

    /**
     * Make sure swagger documents can be crawl-generated successfully.
     *
     * @return void
     */
    public function testMethodGetSwaggerDocumentCrawlSuccess()
    {
        $reflection = self::getReflection($this->controller);
        $reflection->properties->config->setValue($this->controller, array_merge(self::$defaultConfig, [
            'docs' => [
                'crawl' => true
            ],
            'library' => [
                'testdoc' => [
                    'include' => APP . 'src', // all files in dir
                ]
            ]
        ]));

        // make sure all files are being crawled
        $result = $reflection->methods->getSwaggerDocument->invokeArgs($this->controller, ['testdoc']);
        $this->assertSame($result->info->description, 'cakephp-swagger test document'); // IncludeController
        $this->assertSame($result->paths[0]->path, '/taxis'); // ExcludeController

        // make sure exclusions are actually being excluded from crawling.
        $reflection->properties->config->setValue($this->controller, array_merge(self::$defaultConfig, [
            'docs' => [
                'crawl' => true
            ],
            'library' => [
                'testdoc' => [
                    'include' => APP . 'src',
                    'exclude' => APP . 'src' . DS . 'Controller' . DS . 'DummyExcludeController'
                ]
            ]
        ]));

        $result = $reflection->methods->getSwaggerDocument->invokeArgs($this->controller, ['testdoc']);
        $this->assertSame($result->info->description, 'cakephp-swagger test document'); // IncludeController
        $this->assertNotSame($result->paths[0]->path, '/taxis'); // In ExcludeController so should not be present
    }

    /**
     * Make sure an exception is thrown when swagger document cannot be
     * written to the filesystem.
     *
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage Error writing Swagger json document to filesystem
     */
    public function testMethodWriteSwaggerDocumentToFileFail()
    {
        $reflection = self::getReflection($this->controller);
        $reflection->methods->writeSwaggerDocumentToFile->invokeArgs($this->controller, ['////failing-doc-path', 'dummy-file-content']);
    }

    /**
     * Make sure requesting a document that does not exist on the filesystem
     * throws an exception in non-development mode.
     *
     * @expectedException \Cake\Network\Exception\NotFoundException
     * @expectedExceptionMessageRegExp #Swagger json document was not found on filesystem: *#
     */
    public function testMethodGetSwaggerDocumentFromFileFail()
    {
        $reflection = self::getReflection($this->controller);
        $reflection->properties->config->setValue($this->controller, array_merge(self::$defaultConfig, [
            'docs' => [
                'crawl' => false // force loading doc from filesystem
            ],
            'library' => [
                'nonexisting' => []
            ]
        ]));
        $reflection->methods->index->invokeArgs($this->controller, ['nonexisting']);
    }

    /**
     * Make sure swagger documents are successfully served from filesystem.
     *
     * @return void
     */
    public function testMethodGetSwaggerDocumentFromFileSuccess()
    {
        // make sure test file does not exist
        $filePath = CACHE . 'cakephp_swagger_testdoc.json';
        $this->assertFileNotExists($filePath);

        // successfully crawl-generate a fresh json file
        $reflection = self::getReflection($this->controller);
        $reflection->properties->config->setValue($this->controller, array_merge(self::$defaultConfig, [
            'docs' => [
                'crawl' => true
            ],
            'library' => [
                'testdoc' => [
                    'include' => APP . 'src', // all files in dir
                ]
            ]
        ]));

        $result = $reflection->methods->getSwaggerDocument->invokeArgs($this->controller, ['testdoc']);
        $this->assertFileExists($filePath);
        $this->assertSame($result->info->description, 'cakephp-swagger test document');

        // generated file should load from from filesystem when disabling crawl
        $reflection->properties->config->setValue($this->controller, array_merge(self::$defaultConfig, [
            'docs' => [
                'crawl' => false
            ],
            'library' => [
                'testdoc' => [
                    'include' => APP . 'src', // crawl all files in this directory
                ]
            ]
        ]));

        $reflection->methods->getSwaggerDocument->invokeArgs($this->controller, ['testdoc']);
        $fh = new File($filePath);
        $fileContent = $fh->read();
        $this->assertContains('cakephp-swagger test document', $fileContent); // Annotation found in IncludeController
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
                'cors' => []
            ]
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
