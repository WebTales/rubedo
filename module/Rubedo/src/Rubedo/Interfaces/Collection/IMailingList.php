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
 * Interface of service handling Mailing list
 *
 *
 * @author mgoncalves
 * @category Rubedo
 * @package Rubedo
 */
interface IMailingList extends IAbstractCollection
{

    /**
     * Add a user into a specified mailing list
     *
     * @param string $mailingListId            
     * @param string $email            
     * @param boolean $doNotDuplicate            
     *
     * @return array
     */
    public function subscribe ($mailingListId, $email, $doNotDuplicate = true);

    /**
     * Remove a user from a specified mailing list
     *
     * @param string $mailingListId            
     * @param string $email            
     *
     * @return array
     */
    public function unSubscribe ($mailingListId, $email);

    public function getNewMessage ($mailingListId);
}
