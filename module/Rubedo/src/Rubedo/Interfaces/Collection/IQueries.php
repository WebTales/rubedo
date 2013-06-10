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
namespace Rubedo\Interfaces\Collection;

/**
 * Interface of service handling Queries
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IQueries extends IAbstractCollection
{

    /**
     * Return an array of filter and sort params for the query given by its ID
     *
     * result is formatted
     * array(
     * "filter" => $filterArray,
     * "sort" => $sort
     * )
     *
     * @param string $id            
     * @return array | false
     */
    public function getFilterArrayById ($id);

    /**
     * Return an array of filter and sort params for the given query
     *
     * result is formatted
     * array(
     * "filter" => $filterArray,
     * "sort" => $sort
     * )
     *
     * @param array $query            
     * @return array | false
     */
    public function getFilterArrayByQuery ($query);

    /**
     * Return a query
     *
     * @param string $id            
     * @return boolean multitype:
     */
    public function getQueryById ($id = null);
}
