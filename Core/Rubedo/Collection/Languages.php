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

use Rubedo\Interfaces\Collection\IGroups, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

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
                'iso2' => 1,
            ),
            'options' => array(
                'unique' => true
            )
        ),
        array(
            'keys' => array(
                'locale' => 1,
            ),
            'options' => array(
                'unique' => true
            )
        ),
    );

    public function __construct ()
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
    public function findByLocale ($name)
    {
    	$filter = Filter::factory('Value')->setValue($name)->setName('locale');
    	return $this->_dataService->findOne($filter);
    }
    
    /**
     * Find a language given by its ISO-639-1 code (2 letters ISO code)
     * 
     * @param string $iso
     * @return array
     */
    public function findByIso ($iso)
    {
    	$filter = Filter::factory('Value')->setValue($iso)->setName('iso2');
    	return $this->_dataService->findOne($filter);
    }
}
