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

namespace RubedoAPITest\Services\Internationalization;

use Rubedo\Collection\AbstractLocalizableCollection;
use RubedoAPI\Services\Internationalization\Current;
use RubedoAPI\Entities\API\Language;

class CurrentTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var \RubedoAPI\Services\Internationalization\Current
     */
    protected $currentLocalization;

    public function setUp()
    {
        $this->currentLocalization = new Current();
        AbstractLocalizableCollection::setLocalizationStrategy('all');
        AbstractLocalizableCollection::setFallbackLocale(null);
        AbstractLocalizableCollection::setWorkingLocale(null);
        AbstractLocalizableCollection::setIncludeI18n(true);

    }

    public function testInjectLocalizationWithFallback()
    {
        $lang = new Language('fr|en');
        $this->currentLocalization->injectLocalization(array('lang' => $lang));

        $this->assertEquals('fallback', AbstractLocalizableCollection::getLocalizationStrategy());
        $this->assertEquals('en', AbstractLocalizableCollection::getFallbackLocale());

        $this->assertEquals('fr', AbstractLocalizableCollection::getWorkingLocale());
        $this->assertEquals(false, AbstractLocalizableCollection::getIncludeI18n());
    }

    public function testInjectLocalizationWithoutFallback()
    {
        $lang = new Language('fr');
        $this->currentLocalization->injectLocalization(array('lang' => $lang));
        $this->assertEquals('onlyOne', AbstractLocalizableCollection::getLocalizationStrategy());
        $this->assertEquals(null, AbstractLocalizableCollection::getFallbackLocale());

        $this->assertEquals('fr', AbstractLocalizableCollection::getWorkingLocale());
        $this->assertEquals(false, AbstractLocalizableCollection::getIncludeI18n());
    }

    /**
     * @expectedException \RubedoAPI\Exceptions\APIServiceException
     */
    public function testInjectLocalizationExceptionBadParam()
    {
        $this->currentLocalization->injectLocalization(array('lang' => 'fr'));
    }
} 