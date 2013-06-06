<?php
/**
 * Rubedo -- ECM solution Copyright (c) 2013, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Image;

use Rubedo\Interfaces\Image\IImage;

/**
 * Image transofmration service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Image implements IImage
{

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Image\IImage::resizeImage()
     */
    public function resizeImage ($fileName, $mode = null, $width = null, $height = null, $size = null)
    {
        $imgInfos = getimagesize($fileName);
        $imgWidth = $imgInfos[0];
        $imgHeight = $imgInfos[1];
        $mime = $imgInfos['mime'];
        list ($mainType, $type) = explode('/', $mime);
        unset($mainType);
        $gdCreateClassName = 'imagecreatefrom' . $type;
        $image = $gdCreateClassName($fileName);
        
        $ratio = $imgWidth / $imgHeight;
        if ((is_null($width) || $imgWidth == $width) && (is_null($height) || ($imgHeight == $height))) { // do
                                                                                                         // not
                                                                                                         // transform
                                                                                                         // anything
                                                                                                         // :
                                                                                                         // return
                                                                                                         // original
                                                                                                         // image
            $newImage = $image;
            if ($type == 'png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }
        } elseif ($mode == 'morph') { // transform image so that new one fit
                                      // exactly the dimension with anamorphic
                                      // resizing
            $width = isset($width) ? $width : $height * $ratio;
            $height = isset($height) ? $height : $width / $ratio;
            
            $newImage = imagecreatetruecolor($width, $height);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            if ($type == 'gif') {
                imagecolortransparent($newImage, $transparent);
            }
            imagefill($newImage, 0, 0, $transparent);
            if ($type == 'png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);
        } elseif ($mode == 'boxed') { // respect ratio, tallest image which fit
                                      // the box
            if (is_null($width) || is_null($height)) {
                $width = isset($width) ? $width : $height * $ratio;
                $height = isset($height) ? $height : $width / $ratio;
            } else {
                $newRatio = $width / $height;
                // which dimension should be modified
                if ($newRatio > $ratio) {
                    $width = $height * $ratio;
                } else {
                    $height = $width / $ratio;
                }
            }
            $newImage = imagecreatetruecolor($width, $height);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            if ($type == 'gif') {
                imagecolortransparent($newImage, $transparent);
            }
            imagefill($newImage, 0, 0, $transparent);
            
            if ($type == 'png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $imgWidth, $imgHeight);
        } elseif ($mode == 'crop') { // respect ratio but crop part which do not
                                     // fit the box.
            $width = isset($width) ? $width : $imgWidth;
            $height = isset($height) ? $height : $imgHeight;
            
            $widthCoeff = $width / $imgWidth;
            $heightCoeff = $height / $imgHeight;
            $transformCoeff = max($widthCoeff, $heightCoeff);
            
            $tmpWidth = $transformCoeff * $imgWidth;
            $tmpHeight = $transformCoeff * $imgHeight;
            
            $tmpImage = imagecreatetruecolor($tmpWidth, $tmpHeight);
            $transparent = imagecolorallocatealpha($tmpImage, 0, 0, 0, 127);
            if ($type == 'gif') {
                imagecolortransparent($tmpImage, $transparent);
            }
            imagefill($tmpImage, 0, 0, $transparent);
            if ($type == 'png') {
                imagealphablending($tmpImage, false);
                imagesavealpha($tmpImage, true);
            }
            imagecopyresampled($tmpImage, $image, 0, 0, 0, 0, $tmpWidth, $tmpHeight, $imgWidth, $imgHeight);
            
            if ($tmpWidth > $width) {
                $marginWidth = ($tmpWidth - $width) / 2;
            } else {
                $marginWidth = 0;
            }
            
            if ($tmpHeight > $height) {
                $marginHeight = ($tmpHeight - $height) / 2;
            } else {
                $marginHeight = 0;
            }
            
            $newImage = imagecreatetruecolor($width, $height);
            $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
            if ($type == 'gif') {
                imagecolortransparent($newImage, $transparent);
            }
            imagefill($newImage, 0, 0, $transparent);
            if ($type == 'png') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
            }
            imagecopy($newImage, $tmpImage, 0, 0, $marginWidth, $marginHeight, $tmpWidth, $tmpHeight);
            imagedestroy($tmpImage);
        } else {
            throw new \Rubedo\Exceptions\Server("Unimplemented resize mode", "Exception81");
        }
        return $newImage;
    }
}
