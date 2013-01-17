<?php
/**
 * Rubedo -- ECM solution Copyright (c) 2012, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Interfaces\Image;


/**
 * Image transofmration service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
interface IImage
{

    /**
     * Return a gdimage ressource which is a resized version of source imagefile
     * 
     * @param string $fileName
     * @param string $mode
     * @param int $width
     * @param int $height
     * @param string $size
     * @return resource
     */
    public function resizeImage($fileName,$mode=null,$width=null,$height=null,$size=null);
    

}
