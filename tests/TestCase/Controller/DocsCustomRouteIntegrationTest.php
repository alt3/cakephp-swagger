<?php
namespace Alt3\Swagger\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestCase;

class DocsCustomRouteIntegrationTest extends IntegrationTestCase
{

    /**
     * @var string holding full path to temporary swagger.php configuration file.
     */
    public $tempConfig;

    /**
     * setUp method. Creates a temporary swagger.php configuration file
     * specific to this integration test.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->useHttpServer(true);
        $configTemplate = APP . 'config' . DS . 'swagger.php.docs.custom_route';
        $this->tempConfig = APP . 'config' . DS . 'swagger.php';
        copy($configTemplate, $this->tempConfig);
    }

    /**
     * tearDown method executed after every test method.
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unlink($this->tempConfig);

        $testDoc = CACHE . 'cakephp_swagger_testdoc.json';
        if (file_exists($testDoc)) {
            unlink($testDoc);
        }
    }

    /**
     * Make sure the custom document route is connected and serves
     * crawl-generated json with swagger body and CORS headers.
     *
     * @return void
     *
     * @throws \PHPUnit\Exception
     */
    public function testCustomRouteSuccess()
    {
        $this->get('/custom-docs-route/testdoc');
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        $this->assertResponseContains('"swagger": "2.0"');
        $this->assertHeader('Access-Control-Allow-Origin', '*');
        $this->assertHeader('Access-Control-Allow-Headers', 'X-Requested-With');
    }

    /**
     * Make sure the default document route no longer functions.
     * @throws \PHPUnit\Exception
     */
    public function testDefaultRouteFail()
    {
        $this->get('/alt3/swagger/docs');
        $this->assertResponseError();
        $this->assertResponseContains('Error: Missing Route');
    }
}
