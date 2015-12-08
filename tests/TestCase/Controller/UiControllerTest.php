<?php
namespace Alt3\Swagger\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestCase;
use Cake\TestSuite\TestCase;

class UiControllerTest extends IntegrationTestCase
{

    /**
     * Test routes
     *
     * @return void
     **/
    public function testDefaultRoute()
    {
        // default route
        $this->get('/alt3/swagger');
        $this->assertResponseOk();
        $this->assertResponseContains('<body class="swagger-section">');
    }

    public function testDefaultRoute404()
    {
        $this->get('/alt3/swagger/nonexistent');
        $this->assertResponseError();
        $this->assertResponseContains('Error: Missing Route');
    }
}
