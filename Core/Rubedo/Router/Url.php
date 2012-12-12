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
namespace Rubedo\Router;

use Rubedo\Interfaces\Router\IUrl;
use Rubedo\Services\Manager;
/**
 * Front Office URL service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Url implements  IUrl
{

    /**
     * Return page id based on request URL
     *
     * @param string $url requested URL
     * @return string|int
     */
    public function getPageId($url)
    {

        $page = "index";

        $matches = array();
        $regex = '~/index/([^/?]*)~i';
        if (preg_match($regex, $url, $matches)) {
            $page = $matches[1];
        } else {
            $regex = '~/([^/?]*)~i';
            if (preg_match($regex, $url, $matches)) {
                $page = $matches[1];
            }
        }
        
        if(in_array($page, array('','index'))){
            $page = 'accueil';
        }
       $page = Manager::getService('Pages')->findByName($page);
       if($page['id']==''){
           return null;
       }

        return $page['id'];
    }

}
