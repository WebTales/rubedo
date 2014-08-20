<?php

namespace RubedoAPITest\Entities\API;

use RubedoAPI\Entities\API\Identity;

class IdentityTest extends \PHPUnit_Framework_TestCase {

    public function testRetrieveApplicationFromLazyLoader()
    {
        $identityObject = new Identity(null);
        $appService = $identityObject->getApplicationService();
        $this->assertEquals(get_class($appService), 'Zend\Mvc\Application');
    }

    /**
     * @expectedException        \Zend\ServiceManager\Exception\ServiceNotFoundException
     */
    public function testRetrieveFooServiceFromLazyLoader()
    {
        $identityObject = new Identity(null);
        $identityObject->getFooService();
    }

} 