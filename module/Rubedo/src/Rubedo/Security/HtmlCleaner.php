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
namespace Rubedo\Security;

use Rubedo\Interfaces\Security\IHtmlCleaner;
use Rubedo\Services\Manager;
use Rubedo\Services\Events;

/**
 * Service to handle allowed and disallowed HTML contents
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class HtmlCleaner implements IHtmlCleaner
{

    const PRE_HTMLCLEANER = 'rubedo_htmlcleaner_pre';

    const POST_HTMLCLEANER = 'rubedo_htmlcleaner_post';

    /**
     * Clean a raw content to become a valid HTML content without threats
     *
     * @param string $html            
     * @return string
     */
    public function clean($html)
    {
        $hash = Manager::getService('Hash')->hashString($html);
        $key = 'htmlcleaner_' . $hash;
        $response = Events::getEventManager()->trigger(self::PRE_HTMLCLEANER, null, array(
            'key' => $key
        ));
        if ($response->stopped()) {
            return $response->first();
        }
        $result = $this->internalClean($html);
        Events::getEventManager()->trigger(self::POST_HTMLCLEANER, null, array(
            'key' => $key,
            'result' => $result
        ));
        return $result;
    }

    protected function internalClean($html)
    {
        $allowedTags = array(
            'p',
            'div',
            'img',
            'h2',
            'h3',
            'h4',
            'h5',
            'h6'
        );
        $allowedTagString = '<' . implode('><', $allowedTags) . '>';
        $html = strip_tags($html, $allowedTagString);
        return $html;
    }
}
