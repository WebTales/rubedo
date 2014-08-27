<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

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