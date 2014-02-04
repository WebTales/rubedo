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

use Rubedo\Interfaces\Collection\IProducts;
use Rubedo\Services\Events;
/**
 * Service to handle UserTypes
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 */
class Products extends AbstractCollection implements IProducts
{

    public function __construct()
    {
        $this->_collectionName = 'Products';
        parent::__construct();
    }

    public function isTypeUsed($typeId)
    {
        $filter = Filter::factory('Value')->setName('typeId')->setValue($typeId);
        $result = $this->_dataService->findOne($filter, false);
        return ($result != null) ? array(
            "used" => true
        ) : array(
            "used" => false
        );
    }

}
