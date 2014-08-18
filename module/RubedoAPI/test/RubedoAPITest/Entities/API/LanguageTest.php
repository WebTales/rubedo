<?php

namespace RubedoAPITest\Entities\API;

use RubedoAPI\Entities\API\Language;

class LanguageTest extends \PHPUnit_Framework_TestCase {

    public function testConstructWithFallback()
    {
        $language = new Language('en|fr');
        $this->assertEquals($language->getLocale(), 'en');
        $this->assertEquals($language->getFallback(), 'fr');
    }

    public function testConstructWithoutFallback()
    {
        $language = new Language('en');
        $this->assertEquals($language->getLocale(), 'en');

    }

    /**
     * @expectedException        \RubedoAPI\Exceptions\APIEntityException
     */
    public function testConstructLowerInput()
    {
        new Language('f');
    }

    /**
     * @expectedException        \RubedoAPI\Exceptions\APIEntityException
     */
    public function testConstructGreaterInput()
    {
        new Language('enfoofr');
    }
}