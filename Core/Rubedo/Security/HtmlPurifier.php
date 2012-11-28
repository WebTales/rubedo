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

/**
 * Service to handle allowed and disallowed HTML contents
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class HtmlPurifier extends HtmlCleaner
{

    /**
     * Clean a raw content to become a valid HTML content without threats
     *
     * @param string $html
     * @return string
     */
    public function clean($html)
    {
        include_once ('HTMLPurifier/HTMLPurifier.auto.php');

        if (class_exists('\HTMLPurifier_Config')) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Core.Encoding', 'UTF-8');
            $config->set('Cache.SerializerPath', APPLICATION_PATH . "/../cache/htmlpurifier");

            $purifier = new \HTMLPurifier($config);

            $html = $purifier->purify($html);

            return $html;
        } else {
            return parent::clean($html);
        }

    }

}
