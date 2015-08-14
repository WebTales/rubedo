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
namespace Rubedo\Interfaces\Collection;

/**
 * Interface of service handling UserRecommendations
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
interface IUserRecommendations extends IAbstractCollection
{

    /**
     * Returns an array of contents depending upon user's habits (taxonomies associated to consulted contents)
     *
     * @param   int     $limit  Limit of contents returned
     * @return  array   Returns a list of recommended contents for the current user
     */
    public function read($limit = 50);

    /**
     * Create recommendations for the current user in database
     *
     * @return mixed
     */
    public function build();

    /**
     * Delete current user's recommendations
     *
     * @return mixed
     */
    public function flush();

}
