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
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IThemes;
use WebTales\MongoFilters\Filter;

/**
 * Service to handle Themes
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Themes extends AbstractCollection implements IThemes
{

    public function __construct()
    {
        $this->_collectionName = 'Themes';
        parent::__construct();
    }

    public function findByName($name)
    {
        $filter = Filter::factory('Value');
        $filter->setValue(new \MongoRegex("/^".$name."$/i"))->setName('text');
        $obj = $this->_dataService->findOne($filter);
        if ($obj) {
            $obj = $this->_addReadableProperty($obj);
        }
        return $obj;
    }
}
