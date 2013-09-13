<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\ILanguages, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

/**
 * Service to handle Languages
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Languages extends AbstractCollection implements ILanguages
{

    protected $_indexes = array(
        array(
            'keys' => array(
                'iso2' => 1
            )
        ),
        array(
            'keys' => array(
                'active' => 1
            )
        ),
        array(
            'keys' => array(
                'locale' => 1
            ),
            'options' => array(
                'unique' => true
            )
        )
    );

    protected static $activated = null;

    protected static $activeLanguages = array();

    protected static $activeLocalesArray = null;

    protected static $defaultLanguage = null;

    public function __construct()
    {
        $this->_collectionName = 'Languages';
        parent::__construct();
    }

    /**
     * Find a language given by its Locale name
     *
     * @param string $name            
     * @return array
     */
    public function findByLocale($name)
    {
        $filter = Filter::factory('Value')->setValue($name)->setName('locale');
        $result = $this->_dataService->findOne($filter);
        if ($result) {
            $result = $this->_addReadableProperty($result);
        }
        return $result;
    }

    /**
     * Find a language given by its ISO-639-1 code (2 letters ISO code)
     *
     * @param string $iso            
     * @return array
     */
    public function findByIso($iso)
    {
        $filter = Filter::factory('Value')->setValue($iso)->setName('iso2');
        $result = $this->_dataService->findOne($filter);
        
        return $result;
    }

    public function isActivated()
    {
        if (! isset(self::$activated)) {
            $filter = Filter::factory('Value')->setValue(true)->setName('active');
            $result = $this->_dataService->findOne($filter);
            self::$activated = ! is_null($result);
        }
        return self::$activated;
    }

    public function isActive($locale)
    {
        if (! isset(self::$activeLanguages[$locale])) {
            $filters = Filter::factory();
            $filters->addFilter(Filter::factory('Value')->setValue(true)
                ->setName('active'));
            $filters->addFilter(Filter::factory('Value')->setValue($locale)
                ->setName('locale'));
            
            $result = $this->_dataService->findOne($filters);
            self::$activeLanguages[$locale] = ! is_null($result);
        }
        return self::$activeLanguages[$locale];
    }
    
    // todo : set this to "isDefault" when ready
    public function getDefaultLanguage()
    {
        if (! isset(self::$defaultLanguage)) {
            $filter = Filter::factory('Value')->setValue(true)->setName('isDefault');
            $result = $this->_dataService->findOne($filter);
            self::$defaultLanguage = $result;
        }
        if (isset(self::$defaultLanguage)) {
            return self::$defaultLanguage['locale'];
        }
    }

    public function getActiveLanguages($siteId = null)
    {
        $filters = Filter::factory();
        $filters->addFilter(Filter::factory('Value')->setName('active')
            ->setValue(true));
        $result = Manager::getService('Languages')->getList($filters, array(
            array(
                'property' => 'label',
                'direction' => 'ASC'
            )
        ));
        return $result['data'];
    }

    /**
     * Return the list of active locales
     *
     * @return array
     */
    public function getActiveLocales()
    {
        if (! isset(self::$activeLocalesArray)) {
            self::$activeLocalesArray = array();
            foreach ($this->getActiveLanguages() as $item) {
                self::$activeLocalesArray[] = $item['locale'];
            }
        }
        return self::$activeLocalesArray;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy(array $obj, $options = array())
    {
        throw new \Rubedo\Exceptions\User('Languages can\'t be deleted', "Exception100");
    }

    /**
     * Add a readOnly field to contents based on user rights
     *
     * @param array $obj            
     * @return array
     */
    protected function _addReadableProperty($obj)
    {
        list ($label) = explode(';', $obj['label']);
        $obj['label'] = $label;
        return $obj;
    }
}
