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

use WebTales\MongoFilters\Filter;

/**
 * Service to handle Delegations
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Countries extends AbstractCollection
{

    public function __construct()
    {
        $this->_collectionName = 'Countries';
        parent::__construct();
    }

    public function findOneByAlpha2($alpha2)
    {
        $filter = Filter::factory('Value')->setValue($alpha2)->setName('alpha-2');
        $result = $this->_dataService->findOne($filter);
        if ($result) {
            $result = $this->_addReadableProperty($result);
        }
        return $result;
    }
}
