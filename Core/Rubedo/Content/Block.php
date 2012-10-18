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

namespace Rubedo\Content;

require_once (APPLICATION_PATH . '/modules/default/controllers/DataController.php');

Use Rubedo\Interfaces\Content\IBlock;
Use Rubedo\Services\Manager;
/**
 * Block Content Service
 *
 * Get current user and user informations
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Block implements IBlock
{
    /**
     * @param  \Rubedo\Interfaces\Mongo\IDataAccess
     */
    protected $_dataReader;

    /**
     * Return the data associated to a block given by config array
     * @param array $blockConfig bloc options (type, filter params...)
     * @param array $page parent page info
     * @param Zend_Controller_Action $parentController
     * @return array block data to be rendered
     */
    public function getBlockData($blockConfig, $page, $parentController) {

        $this->_page = $page;
        $helper = 'helper' . $blockConfig['Module'];
        $output = $blockConfig['Output'];
        $input = $blockConfig['Input'];
        switch($blockConfig['Module']) {
            case 'BreadCrumb' :
                $content = $this->getBreadCrumb();
                break;
            case 'Carrousel' :
                $content = $this->getCarrousel();
                break;
            case 'ContentList' :
                $content = $this->getContentList();
                break;
            case 'HeadLine' :
                $content = $this->getHeadLine();
                break;
            case 'IFrame' :
                $content = $this->getIFrame();
                break;
            case 'NavBar' :
                $content = $this->getNavBar();
                break;
            case 'PopIn' :
                $content = $this->getPopIn($input);
                break;
            case 'SimpleContent' :
                $content = $this->getSimpleContent($input);
                break;

            default :
                $content = null;
                break;
        }

        return array($output => $content);
    }

    public function getContentById($contentId) {
        $this->_dataReader = Manager::getService('MongoDataAccess');
        $this->_dataReader->init('Contents');
        $content = $this->_dataReader->findById($contentId);
        return $content;
    }

    public function getArrayOfContentByIds($ArrayId) {
        $this->_dataReader = Manager::getService('MongoDataAccess');
        $this->_dataReader->init('Contents');
        $filterArray = array('id' => array('$in' => $ArrayId));
        $this->_dataReader->addFilter($filterArray);
        $contentArray = $this->_dataReader->read();
        return $contentArray;
    }

    /**
     * Return carousel content
     *
     * @return array
     */
    protected function getCarrousel() {
        // get data

        $id = array("201", "202", "203", "204", "205");

        $defaultNamespace = new \Zend_Session_Namespace('Default');
        if (!isset($defaultNamespace->lang))
            $defaultNamespace->lang = "fr";
        $lang = $defaultNamespace->lang;
        $title = \DataController::getXMLAction("200", $lang);
        $output["title"] = $title['title'];
        $output["id"] = "200";
        $data = array();

        $this->_dataReader = Manager::getService('MongoDataAccess');
        $this->_dataReader->init('Contents');
        $filterArray = array('typeId' => '507fcc1cadd92af204000000');
        $this->_dataReader->addFilter($filterArray);
        $filterArray = array('etat' => 'publié');
        $this->_dataReader->addFilter($filterArray);

        $contentArray = $this->_dataReader->read();
        foreach ($contentArray as $vignette) {
            $fields = $vignette['champs'];
            $fields['title'] = $fields['text'];
            unset($fields['text']);
            $data[] = $fields;
        }

        $output["data"] = $data;

        return $output;
    }

    protected function getBreadCrumb() {

        $links = array( array('libelle' => 'Accueil', 'controller' => 'index', 'current' => false), array('libelle' => $this->_page, 'controller' => '#', 'current' => true));

        return ($links);
    }

    protected function getContentList() {
        // get data
        $output = array();
        $id = array("111", "112", "113", "114", "115", "116", "117", "118");

        $defaultNamespace = new \Zend_Session_Namespace('Default');
        if (!isset($defaultNamespace->lang))
            $defaultNamespace->lang = "fr";
        $lang = $defaultNamespace->lang;

        for ($i = 0; $i <= 7; $i++)
            $output[] = \DataController::getXMLAction($id[$i], $lang);

        return $output;
    }

    protected function getHeadLine() {
		
		$mongoId = '507fd4feadd92aa602000000';
		$content = $this->getContentById('507fd4feadd92aa602000000');
		$output = $content['champs'];
        $output["id"] = $id;

        return $output;

    }

    protected function getIFrame() {
        $id = 98324;
        $fr = array('title' => 'Plan d\'accès', 'width' => 526, 'height' => 366, 'frameborder' => 0, 'scrolling' => 'no', 'marginheight' => 0, 'marginwidth' => 0, 'src' => 'http://maps.google.fr/maps?georestrict=input_srcid:d6c0e9367f692930&hl=fr&ie=UTF8&view=map&cid=4303835548001045871&q=Incubateur+Centrale+Paris&ved=0CBkQpQY&ei=gILZTMuBH8Xujgf2ypj_CA&hq=Incubateur+Centrale+Paris&hnear=&iwloc=A&sll=46.75984,1.738281&sspn=11.232446,19.753418&output=embed', );
        $en = array('title' => 'Area map', 'width' => 526, 'height' => 366, 'frameborder' => 0, 'scrolling' => 'no', 'marginheight' => 0, 'marginwidth' => 0, 'src' => 'http://maps.google.fr/maps?georestrict=input_srcid:d6c0e9367f692930&hl=en&ie=UTF8&view=map&cid=4303835548001045871&q=Incubateur+Centrale+Paris&ved=0CBkQpQY&ei=gILZTMuBH8Xujgf2ypj_CA&hq=Incubateur+Centrale+Paris&hnear=&iwloc=A&sll=46.75984,1.738281&sspn=11.232446,19.753418&output=embed', );

        $defaultNamespace = new \Zend_Session_Namespace('Default');
        if (!isset($defaultNamespace->lang))
            $defaultNamespace->lang = "fr";
        $lang = $defaultNamespace->lang;

        $output = $$lang;
        $output['id'] = $id;

        return $output;

    }

    protected function getNavBar() {
        // images examples
        // TODO : load data from services
        $id = "987194";
        // block id
        $responsive = true;
        // responsive : true or false
        $position = "static-top";
        // position : none, fixed-top, fixed-bottom, static-top
        $brand = "Rubedo";
        // brand
        $options = array("loginform", "langselector", "themechooser", "search");
        $fr = array( array('id' => 1, 'type' => 'link', 'caption' => 'A propos', 'href' => '#about', 'colapse' => true, 'modal' => true, 'icon' => 'icon-info-sign'), array('id' => 2, 'type' => 'link', 'caption' => 'Contact', 'href' => '/index/contact', 'colapse' => true, 'modal' => false, 'icon' => 'icon-envelope'), array('id' => 3, 'type' => 'dropdown', 'caption' => 'Rubedo à la loupe', 'colapse' => true, 'modal' => false, 'icon' => 'icon-zoom-in', 'list' => array( array('caption' => 'Mobilité', 'href' => '/index/responsive'), array('caption' => 'Accessibilité', 'href' => '/index/accessible'), array('caption' => 'Performances', 'href' => '/index/performant'), array('caption' => 'Ergonomie', 'href' => '/index/ergonomic'), array('caption' => 'Richesse', 'href' => '/index/rich'), array('caption' => 'Extensibilité', 'href' => '/index/extensible'), array('caption' => 'Robustesse', 'href' => '/index/solid'), array('caption' => 'Pérénité', 'href' => '/index/durable'))));
        $en = array( array('id' => 1, 'type' => 'link', 'caption' => 'About', 'href' => '#about', 'colapse' => true, 'modal' => true, 'icon' => 'icon-info-sign'), array('id' => 2, 'type' => 'link', 'caption' => 'Contact', 'href' => '/index/contact', 'colapse' => true, 'modal' => false, 'icon' => 'icon-envelope'), array('id' => 3, 'type' => 'dropdown', 'caption' => 'Close-up on Rubedo', 'colapse' => true, 'modal' => false, 'icon' => 'icon-zoom-in', 'list' => array( array('caption' => 'Mobile', 'href' => '/index/responsive'), array('caption' => 'Accessible', 'href' => '/index/accessible'), array('caption' => 'Performant', 'href' => '/index/performant'), array('caption' => 'Ergonomic', 'href' => '/index/ergonomic'), array('caption' => 'Rich', 'href' => '/index/rich'), array('caption' => 'Extensible', 'href' => '/index/extensible'), array('caption' => 'Solid', 'href' => '/index/solid'), array('caption' => 'Durable', 'href' => '/index/durable'))));

        $defaultNamespace = new \Zend_Session_Namespace('Default');
        if (!isset($defaultNamespace->lang))
            $defaultNamespace->lang = "fr";
        $lang = $defaultNamespace->lang;

        $output["id"] = $id;
        $output["responsive"] = $responsive;
        $output["position"] = $position;
        $output["brand"] = $brand;
        $output["options"] = $options;
        $output["components"] = $$lang;

        return $output;
    }

    protected function getPopIn($block_id) {
        switch($block_id) {
            case 1 :
                $id = "about";
                $fr = array('title' => 'A propos', 'content' => '
					<div class="modal-body">
					<p>Rubedo est un logiciel open-source de gestion de contenus, développé et maintenu par la société WebTales.</p><p>Rubedo est en phase active de développement.</p><p>Le projet est soutenu par l\'incubateur de l\'Ecole Centrale Paris, et hébergé dans ses locaux.</p>
					</div>
					<div class="modal-footer">
						<a href="#" class="btn" data-dismiss="modal">Fermer</a>
					</div>
					');
                $en = array('title' => 'About', 'content' => '
					<div class="modal-body">
					<p>Rubedo is an open-source content management system, developped and supported by WebTales.</p><p>Rubedo is in an active phase of development.</p><p>This project is supported by Ecole Centrale Paris, and hosted in its offices.</p>
					</div>
					<div class="modal-footer">
						<a href="#" class="btn" data-dismiss="modal">Close</a>
					</div>
					');
                break;
            case 2 :
                $id = "connect";
                $fr = array('title' => 'Connexion', 'content' => '
					<form class="form-horizontal" id="connect">
					  <div class="control-group">
					    <label class="control-label" for="inputEmail">E-mail</label>
					    <div class="controls">
					      <input type="text" id="inputEmail" placeholder="Email" value="julien.bourdin@webtales.fr">
					      <span class="help-inline" id="connect-msg"></span>
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label" for="inputPassword">Mot de passe</label>
					    <div class="controls">
					      <input type="password" id="inputPassword" placeholder="Password" value="webtales">
					    </div>
					  </div>
					  <div class="control-group">
					    <div class="controls">
					      <label class="checkbox">
					        <input type="checkbox"> Maintenir la connexion
					      </label>
					    </div>
					  </div>
					  <div class="modal-footer">
		  				<button type="submit" class="btn btn-primary">Se connecter</button>
						<button type="button" class="btn" data-dismiss="modal">Annuler</button>
					  </div>
					</form>
					');
                $en = array('title' => 'Sign in', 'content' => '
					<form class="form-horizontal" id="connect">
					  <div class="control-group">
					    <label class="control-label" for="inputEmail">E-mail</label>
					    <div class="controls">
					      <input type="text" id="inputEmail" placeholder="Email" value="julien.bourdin@webtales.fr">
					      <span class="help-inline" id="connect-msg"></span>
					    </div>
					  </div>
					  <div class="control-group">
					    <label class="control-label" for="inputPassword">Password</label>
					    <div class="controls">
					      <input type="password" id="inputPassword" placeholder="Password" value="webtales">
					    </div>
					  </div>
					  <div class="control-group">
					    <div class="controls">
					      <label class="checkbox">
					        <input type="checkbox"> Remember me
					      </label>
					    </div>
					  </div>
					  <div class="modal-footer">
		  				<button type="submit" class="btn btn-primary">Sign in</button>
						<button type="button" class="btn" data-dismiss="modal">Cancel</button>
					  </div>
					</form>
					');
                break;
            case 3 :
                $id = "confirm";
                $fr = array('title' => 'Alerte', 'content' => '
					<div class="modal-body">
					<p>Vous êtes sur le point de perdre toutes les modifications effectuées</p>
					</div>
					<div class="modal-footer">
						<button class="btn btn-primary" id="cancel-confirm" data-dismiss="modal">Confirmer</button>
						<a href="#" class="btn" data-dismiss="modal">Annuler</a>
					</div>
					');
                $en = array('title' => 'Alert', 'content' => '
					<div class="modal-body">
					<p>You are about to loose all unsaved modifications</p>
					</div>
					<div class="modal-footer">
						<button class="btn btn-primary" id="cancel-confirm" data-dismiss="modal">Confirm</button>
						<a href="#" class="btn" data-dismiss="modal">Cancel</a>
					</div>
					');
                break;
            case 2 :
        }

        $defaultNamespace = new \Zend_Session_Namespace('Default');
        if (!isset($defaultNamespace->lang))
            $defaultNamespace->lang = "fr";
        $lang = $defaultNamespace->lang;

        $output = $$lang;
        $output['id'] = $id;

        return $output;

    }

    protected function getSimpleContent($block_id) {

        $defaultNamespace = new \Zend_Session_Namespace('Default');
        if (!isset($defaultNamespace->lang))
            $defaultNamespace->lang = "fr";
        $lang = $defaultNamespace->lang;

        $output = \DataController::getXMLAction($block_id, $lang);
        $output["id"] = $block_id;

        return $output;

    }

}
