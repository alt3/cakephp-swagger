<?php
namespace Alt3\Swagger\Test\TestCase\Controller;

use Alt3\Swagger\Controller\DocsController;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\TestSuite\TestCase;
use StdClass;

class DocsControllerTest extends TestCase
{

    /**
     * @var array holding default configuration as found in AppController.
     */
    protected $defaultConfig = [
        'noCache' => true,
        'ui' => [
            'title' => 'cakephp-swagger'
        ]
    ];

    /**
     * tearDown method executed after every testMethod
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
     * Make sure calling docs page without :id parameter throws an exception.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Missing required argument holding Swagger document name
     **/
    public function testMethodIndexMissingArgument()
    {
        $controller = new DocsController();
        $reflection = $this->getIndexReflection($controller);
        $reflection->method->invokeArgs($controller, [null]);
    }

    /**
     * Make sure missing library section in configuration file throws an exception.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Swagger configuration file does not contain a library section
     **/
    public function testMethodIndexMissingLibrarySection()
    {
        $controller = new DocsController();
        $reflection = $this->getIndexReflection($controller);
        $reflection->method->invokeArgs($controller, ['api']);
    }

    /**
     * Make sure missing document definition in configuration file throws an exception.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessageRegExp #Swagger configuration file does not contain a document definition for *#
     **/
    public function testMethodIndexMissingDocumentSection()
    {
        $controller = new DocsController();
        $reflection = $this->getIndexReflection($controller);
        $reflection->property->setValue($controller, array_merge($reflection->property->getValue($controller), [
            'library' => []
        ]));
        $reflection->method->invokeArgs($controller, ['non-existing-document']);
    }

    /**
     * Make sure swagger documents can be crawl-generated successfully.
     *
     * @return void
     **/
    public function testMethodGetSwaggerDocumentRealtimeSuccess()
    {
        $controller = new DocsController();
        $reflection = $reflection = $this->getReflection($controller, 'getSwaggerDocument', 'config');
        $reflection->property->setValue($controller, array_merge($reflection->property->getValue($controller), [
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
        $result = $reflection->method->invokeArgs($controller, ['testdoc']);
        $this->assertTextEquals($result->info->description, 'cakephp-swagger test document'); // IncludeController
        $this->assertTextEquals($result->paths[0]->path, '/taxis'); // ExcludeController

        // make sure exclusions are actually being excluded from crawling.
        $config = $reflection->property->getValue($controller);
        $reflection->property->setValue($controller, array_merge($config, [
            'docs' => [
                'crawl' => true
            ],
            'library' => [
                'testdoc' => [
                    'include' => APP . 'src',
                    'exclude' => APP . 'src' . DS . 'Controller' . DS . 'ExcludeController'
                ]
            ]
        ]));
        $result = $reflection->method->invokeArgs($controller, ['testdoc']);
        $this->assertTextEquals($result->info->description, 'cakephp-swagger test document'); // IncludeController
        $this->assertTextNotEquals($result->paths[0]->path, '/taxis'); // In ExcludeController so should not be present
    }

    /**
     * Make sure an exception is thrown when swagger document cannot be
     * written to the filesystem.
     *
     * @expectedException \Cake\Network\Exception\InternalErrorException
     * @expectedExceptionMessage Error writing Swagger json document to filesystem
     **/
    public function testMethodWriteSwaggerDocumentToFileFail()
    {
        $controller = new DocsController();
        $reflection = $reflection = $this->getReflection($controller, 'writeSwaggerDocumentToFile');
        $reflection->method->invokeArgs($controller, ['////failing-doc-path', 'dummy-file-content']);
    }

    /**
     * Make sure requesting a document that does not exist on the filesystem
     * throws an exception in non-development mode.
     *
     * @expectedException \Cake\Network\Exception\NotFoundException
     * @expectedExceptionMessageRegExp #Swagger json document was not found on filesystem: *#
     **/
    public function testMethodGetSwaggerDocumentFromFileFail()
    {
        $controller = new DocsController();
        $reflection = $this->getIndexReflection($controller);
        $randomDocument = rand(0, 10000000);
        $reflection->property->setValue($controller, array_merge($reflection->property->getValue($controller), [
            'docs' => [
                'crawl' => false // force loading doc from filesystem
            ],
            'library' => [
                $randomDocument => []
            ]
        ]));
        $reflection->method->invokeArgs($controller, [$randomDocument]);
    }

    /**
     * Make sure swagger documents are successfully served from filesystem.
     *
     * @return void
     **/
    public function testMethodGetSwaggerDocumentFromFileSuccess()
    {
        // make sure test file does not exist
        $filePath = CACHE . 'cakephp_swagger_testdoc.json';
        $this->assertFileNotExists($filePath);

        // crawl-generate fresh json file
        $controller = new DocsController();
        $reflection = $reflection = $this->getReflection($controller, 'getSwaggerDocument', 'config');
        $reflection->property->setValue($controller, array_merge($reflection->property->getValue($controller), [
            'docs' => [
                'crawl' => true
            ],
            'library' => [
                'testdoc' => [
                    'include' => APP . 'src', // all files in dir
                ]
            ]
        ]));
        $result = $reflection->method->invokeArgs($controller, ['testdoc']);
        $this->assertFileExists($filePath);
        $this->assertTextEquals($result->info->description, 'cakephp-swagger test document');

        // file is present now so it should be loaded from the filesystem
        $reflection->property->setValue($controller, array_merge($reflection->property->getValue($controller), [
            'docs' => [
                'crawl' => false
            ],
            'library' => [
                'testdoc' => [
                    'include' => APP . 'src', // crawl all files in this directory
                ]
            ]
        ]));
        $reflection->method->invokeArgs($controller, ['testdoc']);
        $fh = new File($filePath);
        $fileContent = $fh->read();
        $this->assertContains('cakephp-swagger test document', $fileContent); // Annotation from IncludeController
    }

    /**
     * Make sure missing or empty CORS headers definition in the configuration
     * file will not trigger adding headers to the json response.
     *
     * @return void
     **/
    public function testAddingCorsHeaders()
    {
        $controller = new DocsController();
        $reflection = $reflection = $this->getReflection($controller, 'addCorsHeaders', 'config');
        $reflection->property->setValue($controller, $this->defaultConfig);

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

    /**
     * Shortcut function to return most used reflection in these tests with
     * default settings.
     *
     * @return
     */
    public function getIndexReflection($controller)
    {
        $controller = new DocsController();
        $reflection = $reflection = $this->getReflection($controller, 'index', 'config');
        $reflection->property->setValue($controller, $this->defaultConfig);
        return $reflection;
    }
}
