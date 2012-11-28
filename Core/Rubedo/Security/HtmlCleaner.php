<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */
namespace Rubedo\Security;

use Rubedo\Interfaces\Security\IHtmlCleaner;

/**
 * Service to handle allowed and disallowed HTML contents
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class HtmlCleaner implements IHtmlCleaner {
    
    /**
     * Clean a raw content to become a valid HTML content without threats
     * 
     * @param string $html
     * @return string
     */
    public function clean($html){
        
        $allowedTags = array('p','div','img','h2','h3','h4','h5','h6');
        $allowedTagString = '<'.implode('><',$allowedTags).'>';
        $html = strip_tags($html,$allowedTagString);
        return $html;
    }

    
}
