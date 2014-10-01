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

namespace RubedoAPI\Interfaces;

/**
 * Interface IResource
 * Define resource methods called by external class
 *
 * @package RubedoAPI\Interfaces
 */
interface IResource
{
    /**
     * Called by entry point to route on the correct action
     *
     * @param $method
     * @param $params
     * @return mixed
     */
    public function handler($method, $params);

    /**
     * Called by entry point to route on the correct action, with ID (entity case)
     *
     * @param $id
     * @param $method
     * @param $params
     * @return mixed
     */
    public function handlerEntity($id, $method, $params);

    /**
     * Save current controller ($this)
     *
     * @param $controller
     * @return mixed
     */
    public function setContext($controller);

    /**
     * Get OPTIONS
     *
     * @return array
     */
    public function optionsAction();

    /**
     * Get OPTIONS for entity
     * @return array
     */
    public function optionsEntityAction();
}