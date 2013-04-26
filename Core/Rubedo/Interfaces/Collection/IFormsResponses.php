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
 * Interface of service handling Delegations
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IFormsResponses extends IAbstractCollection{
    
    /**
     * Get list of finished results for a given form
     * 
     * @param string $formId
     */
    public function getValidResponsesByFormId($formId);
    /**
     * Get count of finished results for a given form
     *
     * @param string $formId
     */
    public function countValidResponsesByFormId($formId);
    /**
     * Get count of unfinished results for a given form
     *
     * @param string $formId
     */
    public function countInvalidResponsesByFormId($formId);
    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\IFormsResponses::getValidResponsesByFormId()
     */
    public function getResponsesByFormId($formId, $start = null, $limit = null);
}
