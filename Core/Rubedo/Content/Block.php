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
Use Rubedo\Controller\Action;

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
     * Read service to access data in mongoDB
     *
     * @param  \Rubedo\Interfaces\Mongo\IDataAccess
     */
    protected $_dataReader;

    /**
     * Return the data associated to a block given by config array
     * @param array $block bloc options (type, filter params...)
     * @return array block data to be rendered
     */
    public function getBlockData($block) {
        switch($block['title']) {
            case 'menus' :
                $controller = 'nav-bar';
                break;
            case 'hero' :
                $controller = 'content-single';
                break;
            case 'carrousel' :
                $controller = 'carrousel';
                break;
            case 'accroches' :
                $controller = 'content-list';
                break;
            case 'footer' :
                $controller = 'footer';
                break;
            case 'recherche' :
                $controller = 'search';
                break;
            case 'responsive' :
                $controller = 'responsive';
                break;
            default :
                $data = array();
                $template = 'root/block.html';
                return array('data' => $data, 'template' => $template);
                break;
        }

        $response = Action::getInstance()->action('index', $controller, 'blocks');
        $data = $response->getBody('content');
        $template = $response->getBody('template');
        return array('data' => $data, 'template' => $template);

    }

    /**
     * Get content by mongoId
     * @param int $contentId
     * @return array
     */
    public function getContentById($contentId) {
        $this->_dataReader = Manager::getService('MongoDataAccess');
        $this->_dataReader->init('Contents');
        $content = $this->_dataReader->findById($contentId);
        return $content;
    }

    /**
     * Get an array of contents by an array of mongoId
     * @param array $arrayId
     * @return array
     */
    public function getArrayOfContentByIds($arrayId) {
        $this->_dataReader = Manager::getService('MongoDataAccess');
        $this->_dataReader->init('Contents');
        $filterArray = array('id' => array('$in' => $arrayId));
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

        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');

        $headerId = '507ff6a8add92a5809000000';
        $header = $this->getContentById($headerId);
        $output["title"] = $header['text'];
        $output["id"] = $headerId;
        $data = array();

        $this->_dataReader = Manager::getService('MongoDataAccess');
        $this->_dataReader->init('Contents');

        $filterArray = array('typeId' => '507fcc1cadd92af204000000');
        $this->_dataReader->addFilter($filterArray);
        $filterArray = array('status' => 'published');
        $this->_dataReader->addFilter($filterArray);

        $contentArray = $this->_dataReader->read();
        foreach ($contentArray as $vignette) {
            $fields = $vignette['fields'];
            $fields['title'] = $fields['text'];
            unset($fields['text']);
            $fields['id'] = (string)$vignette['id'];
            $data[] = $fields;
        }

        $output["data"] = $data;

        return $output;
    }

    /**
     * Return data for breadcrumb block
     * @return array
     */
    protected function getBreadCrumb() {

        $links = array( array('libelle' => 'Accueil', 'controller' => 'index', 'current' => false), array('libelle' => $this->_page, 'controller' => '#', 'current' => true));

        return ($links);
    }

    /**
     * Return data for content list block
     * @return array
     */
    protected function getContentList() {
        // get data
        $output = array();

        $this->_dataReader = Manager::getService('MongoDataAccess');
        $this->_dataReader->init('Contents');
        $filterArray = array('typeId' => '507fea58add92a5108000000');
        $this->_dataReader->addFilter($filterArray);
        $filterArray = array('status' => 'published');
        $this->_dataReader->addFilter($filterArray);
        $this->_dataReader->addSort(array('text' => 'asc'));

        $contentArray = $this->_dataReader->read();
        foreach ($contentArray as $vignette) {
            $fields = $vignette['fields'];
            $fields['title'] = $fields['text'];
            unset($fields['text']);
            $fields['id'] = (string)$vignette['id'];
            $data[] = $fields;
        }
        return $data;
    }

    /**
     * Return data for headline block
     * @return array
     */
    protected function getHeadLine() {

        $mongoId = '507fd4feadd92aa602000000';
        $content = $this->getContentById('507fd4feadd92aa602000000');
        $output = $content['fields'];
        $output["id"] = $mongoId;

        return $output;

    }

    /**
     * Return data for iframe block
     * @return array
     */
    protected function getIFrame() {
        $id = 98324;
        $fr = array('title' => 'Plan d\'accès', 'width' => 526, 'height' => 366, 'frameborder' => 0, 'scrolling' => 'no', 'marginheight' => 0, 'marginwidth' => 0, 'src' => 'http://maps.google.fr/maps?georestrict=input_srcid:d6c0e9367f692930&hl=fr&ie=UTF8&view=map&cid=4303835548001045871&q=Incubateur+Centrale+Paris&ved=0CBkQpQY&ei=gILZTMuBH8Xujgf2ypj_CA&hq=Incubateur+Centrale+Paris&hnear=&iwloc=A&sll=46.75984,1.738281&sspn=11.232446,19.753418&output=embed', );
        $en = array('title' => 'Area map', 'width' => 526, 'height' => 366, 'frameborder' => 0, 'scrolling' => 'no', 'marginheight' => 0, 'marginwidth' => 0, 'src' => 'http://maps.google.fr/maps?georestrict=input_srcid:d6c0e9367f692930&hl=en&ie=UTF8&view=map&cid=4303835548001045871&q=Incubateur+Centrale+Paris&ved=0CBkQpQY&ei=gILZTMuBH8Xujgf2ypj_CA&hq=Incubateur+Centrale+Paris&hnear=&iwloc=A&sll=46.75984,1.738281&sspn=11.232446,19.753418&output=embed', );

        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');

        $output = $$lang;
        $output['id'] = $id;

        return $output;

    }

    /**
     * Return data for navBar block
     * @return array
     */
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

        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');

        $output["id"] = $id;
        $output["responsive"] = $responsive;
        $output["position"] = $position;
        $output["brand"] = $brand;
        $output["options"] = $options;
        $output["components"] = $$lang;

        return $output;
    }

    /**
     * Return data for popin block
     * @param string $block_id
     * @return array
     */
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

        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');

        $output = $$lang;
        $output['id'] = $id;

        return $output;

    }

    /**
     * Return data for simple content block
     * @param string $block_id
     * @return array
     */
    protected function getSimpleContent($block_id) {

        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');

        $output = \DataController::getXMLAction($block_id, $lang);
        $output["id"] = $block_id;

        return $output;

    }

    /**
     * Return data for search block
     * @return array
     */
    protected function getSearch($parentController) {

        // get query
        $terms = $parentController->getRequest()->getParam('query');

        // get type filter
        $type = $parentController->getRequest()->getParam('type');

        // get lang filter
        $session = Manager::getService('Session');
        $lang = $session->get('lang', 'fr');

        // get author filter
        $author = $parentController->getRequest()->getParam('author');

        // get date filter
        $date = $parentController->getRequest()->getParam('date');

        // get pager
        $pager = $parentController->getRequest()->getParam('pager');
        if ($pager == '')
            $pager = 0;

        // get orderBy
        $orderBy = $parentController->getRequest()->getParam('orderby');
        if ($orderBy == '')
            $orderBy = "_score";

        // get page size
        $pageSize = $parentController->getRequest()->getParam('pagesize');
        if ($pageSize == '')
            $pageSize = 10;

        $query = \Rubedo\Services\Manager::getService('ElasticDataSearch');
        $query->init();

        $elasticaResultSet = $query->search($terms, $type, $lang, $author, $date, $pager, $orderBy, $pageSize);

        // Get total hits
        $nbResults = $elasticaResultSet->getTotalHits();
        if ($pageSize != "all") {
            $pageCount = intval($nbResults / $pageSize) + 1;
        } else {
            $pageCount = 1;
        }

        // Get facets from the result of the search query
        $elasticaFacets = $elasticaResultSet->getFacets();

        $elasticaResults = $elasticaResultSet->getResults();

        $results = array();

        foreach ($elasticaResults as $result) {

            $data = $result->getData();
            $resultType = $result->getType();
            //$lang_id = explode('_',$result->getId());
            //$id = $lang_id[1];
            $id = "0";

            $score = $result->getScore();

            if (!is_float($score))
                $score = 1;

            //$url = $data['canonical_url'];
            //if ($url == '') {
            // no canonical url
            // redirect to default detail page
            //$url = '/detail/index/id/'.$id;
            $url = "#";
            //}

            $results[] = array('id' => $id, 'url' => $url, 'score' => $score, 'title' => $data['text'], 'abstract' => $data['abstract'], 'author' => $data['author'], 'type' => $resultType, 'lastUpdateTime' => $data['lastUpdateTime'], );
        }

        $output['searchTerms'] = $terms;
        $output['results'] = $results;
        $output['nbResults'] = $nbResults;
        $output['pager'] = $pager;
        $output['pageCount'] = $pageCount;
        $output['pageSize'] = $pageSize;
        $output['orderBy'] = $orderBy;

        $output['typeFacets'] = $elasticaFacets['typeFacet']['terms'];
        $output['authorFacets'] = $elasticaFacets['authorFacet']['terms'];
        $output['dateFacets'] = $elasticaFacets['dateFacet']['entries'];
        $output['type'] = $type;
        $output['lang'] = $lang;
        $output['author'] = $author;
        $output['date'] = $date;

        $output['termSearchRoot'] = '/index/search?query=' . $terms;
        $output['typeSearchRoot'] = $output['termSearchRoot'];
        $output['authorSearchRoot'] = $output['termSearchRoot'];
        $output['dateSearchRoot'] = $output['termSearchRoot'];
        $output['orderBySearchRoot'] = $output['termSearchRoot'];
        $output['pageSizeSearchRoot'] = $output['termSearchRoot'];
        $output['searchRoot'] = $output['termSearchRoot'];

        if ($author != '') {
            $output['typeSearchRoot'] .= '&author=' . $author;
            $output['dateSearchRoot'] .= '&author=' . $author;
            $output['orderBySearchRoot'] .= '&author=' . $author;
            $output['pageSizeSearchRoot'] .= '&author=' . $author;
            $output['searchRoot'] .= '&author=' . $author;
        }

        if ($type != '') {
            $output['authorSearchRoot'] .= '&type=' . $type;
            $output['dateSearchRoot'] .= '&type=' . $type;
            $output['orderBySearchRoot'] .= '&type=' . $type;
            $output['pageSizeSearchRoot'] .= '&type=' . $type;
            $output['searchRoot'] .= '&type=' . $type;
        }

        if ($date != '') {
            $output['typeSearchRoot'] .= '&date=' . $date;
            $output['authorSearchRoot'] .= '&date=' . $date;
            $output['orderBySearchRoot'] .= '&date=' . $date;
            $output['pageSizeSearchRoot'] .= '&date=' . $date;
            $output['searchRoot'] .= '&date=' . $date;
        }

        if ($orderBy != '') {
            $output['typeSearchRoot'] .= '&orderby=' . $orderBy;
            $output['dateSearchRoot'] .= '&orderby=' . $orderBy;
            $output['authorSearchRoot'] .= '&orderby=' . $orderBy;
            $output['pageSizeSearchRoot'] .= '&orderby=' . $orderBy;
            $output['searchRoot'] .= '&orderby=' . $orderBy;
        }

        if ($pageSize != '') {
            $output['typeSearchRoot'] .= '&pagesize=' . $pageSize;
            $output['dateSearchRoot'] .= '&pagesize=' . $pageSize;
            $output['authorSearchRoot'] .= '&pagesize=' . $pageSize;
            $output['orderBySearchRoot'] .= '&pagesize=' . $pageSize;
            $output['searchRoot'] .= '&pagesize=' . $pageSize;
        }

        return ($output);

    }

}
