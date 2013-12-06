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
 * Interface of service handling Emails
 *
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
interface IEmails extends IAbstractCollection
{
    /**
     * Build the html from body properties and rows
     *
     * @param string $title
     * @param array $bodyProperties
     * @param array $rows
     * @param bool $cid transform image in cid or link ?
     *
     * @return String html
     */
    public function htmlConstructor($title, array $bodyProperties, array $rows, $cid = true);
}
