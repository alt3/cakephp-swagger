<?php
namespace Alt3\Swagger\Test\TestCase\Controller;

use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\DocsControllerTest Test Case
 */
class DocsControllerTest extends IntegrationTestCase
{
    /**
     * Test swagger UI index using default route
     *
     * @return void
     **/
    public function testUiIndexDefaultRoute()
    {
        $this->get('/alt3/swagger');
        $this->assertResponseOk();
        $this->assertResponseContains('<body class="swagger-section">');
    }
}
