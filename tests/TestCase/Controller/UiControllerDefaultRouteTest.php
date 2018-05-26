<?php
namespace Alt3\Swagger\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestCase;

class UiControllerDefaultRouteTest extends IntegrationTestCase
{

    /**
     * @var string Full path to temporary swagger.php configuration file.
     */
    public $tempConfig;

    /**
     * setUp method.
     */
    public function setUp()
    {
        parent::setUp();
        $this->useHttpServer(true);
        $configTemplate = APP . 'config' . DS . 'swagger.php.ui.default_routes';
        $this->tempConfig = APP . 'config' . DS . 'swagger.php';
        copy($configTemplate, $this->tempConfig);
    }

    /**
     * tearDown method.
     */
    public function tearDown()
    {
        parent::tearDown();
        unlink($this->tempConfig);
    }

    /**
     * Make sure the default UI route is connected and serves Petstore document.
     *
     * @return void
     *
     * @throws \PHPUnit\Exception
     */
    public function testDefaultRouteSuccess()
    {
        $this->get('/alt3/swagger');
        $this->assertResponseOk();
        $this->assertResponseContains('<body class="swagger-section">');
        $this->assertResponseContains('http://petstore.swagger.io/v2/swagger.json');
    }

    /**
     * Make sure the custom UI route is not connected.
     *
     * @return void
     *
     * @throws \PHPUnit\Exception
     */
    public function testCustomRouteFail()
    {
        $this->get('/alt3/custom-ui-route');
        $this->assertResponseError();
        $this->assertResponseContains('Error: Missing Route');
    }
}
