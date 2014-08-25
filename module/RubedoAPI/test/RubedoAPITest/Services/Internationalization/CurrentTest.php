<?php

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
        $this->assertEquals('all', AbstractLocalizableCollection::getLocalizationStrategy());
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