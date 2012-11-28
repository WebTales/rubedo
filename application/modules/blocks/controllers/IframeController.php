<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */

Use Rubedo\Services\Manager;

require_once ('AbstractController.php');
/**
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_IframeController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction() {
        $id = 98324;
        $fr = array('title' => 'Plan d\'acces', 'width' => 526, 'height' => 366, 'frameborder' => 0, 'scrolling' => 'no', 'marginheight' => 0, 'marginwidth' => 0, 'src' => 'http://maps.google.fr/maps?georestrict=input_srcid:d6c0e9367f692930&hl=fr&ie=UTF8&view=map&cid=4303835548001045871&q=Incubateur+Centrale+Paris&ved=0CBkQpQY&ei=gILZTMuBH8Xujgf2ypj_CA&hq=Incubateur+Centrale+Paris&hnear=&iwloc=A&sll=46.75984,1.738281&sspn=11.232446,19.753418&output=embed', );
        $en = array('title' => 'Area map', 'width' => 526, 'height' => 366, 'frameborder' => 0, 'scrolling' => 'no', 'marginheight' => 0, 'marginwidth' => 0, 'src' => 'http://maps.google.fr/maps?georestrict=input_srcid:d6c0e9367f692930&hl=en&ie=UTF8&view=map&cid=4303835548001045871&q=Incubateur+Centrale+Paris&ved=0CBkQpQY&ei=gILZTMuBH8Xujgf2ypj_CA&hq=Incubateur+Centrale+Paris&hnear=&iwloc=A&sll=46.75984,1.738281&sspn=11.232446,19.753418&output=embed', );

        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');

        $output = $$lang;
        $output['id'] = $id;
		
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/iframe.html");

        $css = array('/css/rubedo.css', '/css/bootstrap-responsive.css', '/css/default.bootstrap.min.css');
        $js = array("/js/jquery.js", "/js/bootstrap-transition.js", "/js/bootstrap-alert.js", "/js/bootstrap-modal.js", "/js/bootstrap-dropdown.js", "/js/bootstrap-scrollspy.js", "/js/bootstrap-tab.js", "/js/bootstrap-tooltip.js", "/js/bootstrap-popover.js", "/js/bootstrap-button.js", "/js/bootstrap-collapse.js", "/js/bootstrap-carousel.js", "/js/bootstrap-typeahead.js", );

        $this->_sendResponse($output, $template, $css, $js);
    }

}
