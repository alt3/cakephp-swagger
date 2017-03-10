<?php
namespace Alt3\Swagger\Test\TestCase\Lib;

use Alt3\Swagger\Controller\AppController;
use Alt3\Swagger\Lib\SwaggerTools;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Filesystem\File;
use Cake\TestSuite\TestCase;
use StdClass;
use Swagger\Annotations\Swagger;

class SwaggerToolsTest extends TestCase
{

    /**
     * @var \Alt3\Swagger\Lib\SwaggerTools Swaggertools instance.
     */
    protected $lib;

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
        $this->lib = new SwaggerTools();
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
        $this->assertSame(self::$defaultConfig, AppController::$defaultConfig);
    }

    /**
     * Make sure swagger documents can be crawl-generated successfully.
     */
    public function testMethodGetSwaggerDocumentCrawlSuccess()
    {
        $reflection = self::getReflection($this->lib);
        Configure::write('Swagger', array_merge(self::$defaultConfig, [
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
        $result = $reflection->methods->getSwaggerDocument->invokeArgs($this->lib, ['testdoc', 'www.test.app']);
        $this->assertSame($result->info->description, 'cakephp-swagger test document'); // IncludeController
        $this->assertSame($result->paths[0]->path, '/taxis'); // ExcludeController

        // make sure exclusions are actually being excluded from crawling.
        Configure::write('Swagger', array_merge(self::$defaultConfig, [
            'docs' => [
                'crawl' => true
            ],
            'library' => [
                'testdoc' => [
                    'include' => APP . 'src',
                    'exclude' => APP . 'src' . DS . 'Controller' . DS . 'DummyExcludeController'
                ]
            ],
            'analyser' => new \Swagger\StaticAnalyser()
        ]));

        $result = $reflection->methods->getSwaggerDocument->invokeArgs($this->lib, ['testdoc', 'www.test.app']);
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
        $reflection = self::getReflection($this->lib);
        $reflection->methods->writeSwaggerDocumentToFile->invokeArgs($this->lib, ['////failing-doc-path', 'dummy-file-content']);
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
        $reflection = self::getReflection($this->lib);
        Configure::write('Swagger', array_merge(self::$defaultConfig, [
            'docs' => [
                'crawl' => false // force loading doc from filesystem
            ],
            'library' => [
                'nonexisting' => []
            ]
        ]));
        $reflection->methods->getSwaggerDocument->invokeArgs($this->lib, ['nonexisting', 'www.test.app']);
    }

    /**
     * Make sure swagger documents are successfully served from filesystem.
     */
    public function testMethodGetSwaggerDocumentFromFileSuccess()
    {
        // make sure test file does not exist
        $filePath = CACHE . 'cakephp_swagger_testdoc.json';
        $this->assertFileNotExists($filePath);

        // successfully crawl-generate a fresh json file
        $reflection = self::getReflection($this->lib);
        Configure::write('Swagger', array_merge(self::$defaultConfig, [
            'docs' => [
                'crawl' => true
            ],
            'library' => [
                'testdoc' => [
                    'include' => APP . 'src', // all files in dir
                ]
            ]
        ]));

        $result = $reflection->methods->getSwaggerDocument->invokeArgs($this->lib, ['testdoc', 'www.test.app']);
        $this->assertFileExists($filePath);
        $this->assertSame($result->info->description, 'cakephp-swagger test document');

        // generated file should load from from filesystem when disabling crawl
        Configure::write('Swagger', array_merge(self::$defaultConfig, [
            'docs' => [
                'crawl' => false
            ],
            'library' => [
                'testdoc' => [
                    'include' => APP . 'src', // crawl all files in this directory
                ]
            ]
        ]));

        $reflection->methods->getSwaggerDocument->invokeArgs($this->lib, ['testdoc', 'www.test.app']);
        $fh = new File($filePath);
        $fileContent = $fh->read();
        $this->assertContains('cakephp-swagger test document', $fileContent); // Annotation found in IncludeController
    }

    /**
     * Make sure missing library in the configuration throws an exception
     * when running the shell `makedocs` command.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Swagger configuration file does not contain a library section
     */
    public function testMakeDocsWithMissingLibrary()
    {
        $reflection = self::getReflection($this->lib);
        Configure::delete('Swagger.library');
        $reflection->methods->makeDocs->invokeArgs($this->lib, ['www.test.app']);
    }

    /**
     * Make sure library without docs in the configuration throws an exception
     * when running the shell `makedocs` command.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Swagger configuration file does not contain a library section
     */
    public function testMakeDocsWithEmptyLibrary()
    {
        $reflection = self::getReflection($this->lib);
        Configure::write('Swagger.library', [

        ]);
        $reflection->methods->makeDocs->invokeArgs($this->lib, ['www.test.app']);
    }

    /**
     * Make sure makedocs foreach loop is reached with doc-filled library.
     */
    public function testMakeDocsWithFilledLibrary()
    {
        $reflection = self::getReflection($this->lib);
        Configure::write('Swagger.library', [
            'testdoc' => [
                'include' => APP . 'src', // crawl all files in this directory
            ]
        ]);
        $result = $reflection->methods->makeDocs->invokeArgs($this->lib, ['www.test.app']);
        $this->assertTrue($result);
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
