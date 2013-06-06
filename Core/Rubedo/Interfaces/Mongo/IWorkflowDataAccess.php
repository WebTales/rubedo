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
namespace Rubedo\Interfaces\Mongo;

/**
 * Interface of data access services
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IWorkflowDataAccess extends IDataAccess
{

    /**
     * Set the current workspace to workspace
     */
    public function setWorkspace ();

    /**
     * Set the current workspace to live
     */
    public function setLive ();

    /**
     * Publish a content
     */
    public function publish ($objectId);
}
