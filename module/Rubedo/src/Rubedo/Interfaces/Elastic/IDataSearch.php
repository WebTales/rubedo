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
namespace Rubedo\Interfaces\Elastic;

/**
 * Interface of data search services
 *
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
interface IDataSearch
{

    /**
     * Initialize a search service handler to index or search data
     *
     * @param string $host
     *            http host name
     * @param string $port
     *            http port
     */
    public function init($host = null, $port = null);

    /**
     * Create ES type for new content type
     *
     * @param array $params
     * @param string $option
     * @param bool $withSummary
     *
     * @return \Elastica\ResultSet
     */
    public function search(array $params, $option = 'all', $withSummary = true);

    /**
     * Set a param with
     *
     * @param boolean $_isFrontEnd
     */
    public static function setIsFrontEnd($_isFrontEnd);
}