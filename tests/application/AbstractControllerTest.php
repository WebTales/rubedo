<?php

abstract class AbstractControllerTest extends Zend_Test_PHPUnit_ControllerTestCase
{

       public function setUp()
    {
        $this->bootstrap = new Zend_Application('testing', APPLICATION_PATH . '/configs/application.ini');

        parent::setUp();
    }

    public function tearDown()
    {
        $this->resetRequest();
        $this->resetResponse();
        Rubedo\Services\Manager::resetMocks();
        parent::tearDown();
    }


}

