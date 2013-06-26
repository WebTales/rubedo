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
 * Interface of service handling Directories
 *
 *
 * @author aDobre
 * @category Rubedo
 * @package Rubedo
 * @todo fill phpdoc
 */
interface IDirectories extends IAbstractCollection
{


    public function getListByFilePlanId ($filePlanId);

    public function deleteByFilePlanId ($id);

    public function clearOrphanDirectories ();

    public function countOrphanDirectories ();

    public function propagateWorkspace ($parentId, $workspaceId, $filePlanId = null);
    
    /**
     * Set the directory for dam items given by an array of ID
     *
     * @param unknown $arrayId
     * @param unknown $directoryId
     * @throws Rubedo\Exceptions\User
     */
    public function classify($arrayId, $directoryId);
}
