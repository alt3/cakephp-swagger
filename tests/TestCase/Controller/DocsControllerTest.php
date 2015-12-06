<?php
namespace Alt3\Swagger\Test\TestCase\Controller;

use Cake\Core\Configure;
use Cake\TestSuite\IntegrationTestCase;

/**
 * AppControllerTest class
 */
class DocsControllerTest extends IntegrationTestCase
{
    /**
     *  @expectedException \InvalidArgumentException
     */
    public function testMissingId()
    {
        $this->get('/alt3/swagger/docs');
    }
}
