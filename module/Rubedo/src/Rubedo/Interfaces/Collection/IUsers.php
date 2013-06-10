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
 * Interface of service handling users
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IUsers extends IAbstractCollection
{

    /**
     * Change the password of the user given by its id
     * Check version conflict
     *
     * @param string $$password
     *            new password
     * @param int $version
     *            version number
     * @param string $userId
     *            id of the user to be changed
     */
    public function changePassword ($password, $version, $userId);

    /**
     * Return the user associeted to the id
     *
     * @param string $email            
     *
     * @return array
     */
    public function findByEmail ($email);

    public function findValidatingUsersByWorkspace ($workspace);

    public function getAdminUsers ();
}
