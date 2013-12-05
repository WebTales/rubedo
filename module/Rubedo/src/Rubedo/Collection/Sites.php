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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\ISites;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Json\Json;

;

/**
 * Service to handle Sites
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Sites extends AbstractLocalizableCollection implements ISites
{

    protected static $nonLocalizableFields = array(
        "text",
        "alias",
        "defaultLanguage",
        "languages",
        "activeMessagery",
        "SMTPServer",
        "SMTPPort",
        "SMTPLogin",
        "SMTPPassword",
        "defaultEmail",
        "accessibilityLevel",
        "opquastLogin",
        "opquastPassword",
        "protocol",
        "filter",
        "theme",
        "homePage",
        "workspace",
        "readOnly",
        "defaultSingle",
        "googleMapsKey",
        "googleAnalyticsKey",
        "disqusKey",
        "builtOnEmptySite",
        "builtOnModelSiteId",
        "locStrategy",
        "useBrowserLanguage",
        "staticDomain",
        "recaptcha_public_key",
        "recaptcha_private_key"
    );

    protected $_indexes = array(
        array(
            'keys' => array(
                'text' => 1
            ),
            'options' => array(
                'unique' => true
            )
        ),
        array(
            'keys' => array(
                'workspace' => 1
            )
        )
    );

    protected static $_overrideSiteName = array();

    protected static $_overrideSiteNameReverse = array();

    /**
     * Only access to content with read access
     *
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init()
    {
        parent::_init();

        if (!self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (in_array('all', $readWorkspaceArray)) {
                return;
            }
            $filter = Filter::factory('In')->setName('workspace')->setValue($readWorkspaceArray);
            $this->_dataService->addFilter($filter);
        }
    }

    /**
     * set the overrides
     *
     * @param array $array
     */
    public static function setOverride(array $array = null)
    {
        $newArray = array();
        if ($array == null) {
            $array = array();
        }
        foreach ($array as $key => $value) {

            $newArray[str_replace('_', '.', $key)] = str_replace('_', '.', $value);
        }
        self::$_overrideSiteName = $newArray;
        self::$_overrideSiteNameReverse = array_flip($newArray);
    }

    /**
     * call parent and load site overrides
     */
    public function __construct()
    {
        $this->_collectionName = 'Sites';
        parent::__construct();
        $config = Manager::getService('config');
        $options = $config['site'];
        if (isset($options['override'])) {
            self::setOverride($options['override']);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\ISites::getHost()
     */
    public function getHost($site)
    {
        if (is_string($site)) {
            $site = $this->findById($site);
        }
        $label = $site['text'];
        if (isset(self::$_overrideSiteName[$label])) {
            $label = self::$_overrideSiteName[$label];
        }
        return $label;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\ISites::findByHost()
     */
    public function findByHost($host)
    {
        if (isset(self::$_overrideSiteNameReverse[$host])) {
            $host = self::$_overrideSiteNameReverse[$host];
        }

        $site = $this->findByName($host);
        if ($site === null) {
            $filter = Filter::factory('Value');
            $filter->setName('alias')->setValue($host);
            $site = $this->_dataService->findOne($filter);
        }

        return $site;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Collection\ISites::deleteById()
     */
    public function deleteById($id)
    {
        $mongoId = $this->_dataService->getId($id);
        return $this->_dataService->customDelete(array(
            '_id' => $mongoId
        ));
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy(array $obj, $options = array())
    {
        if ($this->_isReadable($obj)) {
            $id = $obj['id'];
            $pages = \Rubedo\Services\Manager::getService('Pages')->deleteBySiteId($id);
            if ($pages['ok'] == 1) {
                $masks = \Rubedo\Services\Manager::getService('Masks')->deleteBySiteId($id);
                if ($masks['ok'] == 1) {
                    $returnArray = parent::destroy($obj, $options);
                } else {
                    $returnArray = array(
                        'success' => false,
                        'msg' => "error during masks deletion"
                    );
                }
            } else {
                $returnArray = array(
                    'success' => false,
                    'msg' => "error during pages deletion"
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                'msg' => "you don't have the permission to delete this site"
            );
        }

        return $returnArray;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update(array $obj, $options = array())
    {
        $obj = $this->_initContent($obj);

        $return = parent::update($obj, $options);
        if ($return['success'] == true) {
            Manager::getService('Pages')->propagateWorkspace('root', $return['data']['workspace'], $return['data']['id']);
        }
        return $return;
    }

    /**
     * add workspace on a site object based on current user
     *
     * @param array $site
     * @return array
     */
    protected function _setDefaultWorkspace($site)
    {
        if (!isset($site['workspace']) || $site['workspace'] == '' || $site['workspace'] == array()) {
            $mainWorkspace = Manager::getService('CurrentUser')->getMainWorkspace();
            $site['workspace'] = $mainWorkspace['id'];
        }
        return $site;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create(array $obj, $options = array())
    {
        $obj = $this->_setDefaultWorkspace($obj);
        $obj = $this->_initContent($obj);

        return parent::create($obj, $options);
    }

    protected function _initContent($obj)
    {
        // verify workspace can be attributed
        if (!self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            if (!in_array($obj['workspace'], $writeWorkspaces)) {
                throw new \Rubedo\Exceptions\Access('You can not assign to this workspace', "Exception35");
            }
        }

        return $obj;
    }

    public function createFromModel($insertData)
    {
        $model = $this->_dataService->findById($insertData['builtOnModelSiteId']);
        if (empty($model)) {
            $returnArray = array(
                'success' => false,
                "msg" => 'site model not found'
            );
            return ($returnArray);
        }
        $masksService = Manager::getService('Masks');
        $pagesService = Manager::getService('Pages');
        $queriesService = Manager::getService('Queries');
        $contentsService = Manager::getService('Contents');
        $oldIdArray = array();
        $theBigString = "";

        $modelId = $model['id'];
        $oldIdArray[] = $modelId;
        $theBigString = $theBigString . Json::encode($model);
        $theBigString = $theBigString . "SEntityS";

        $oldMaskFilters = Filter::factory('value')->setName('site')->setValue($modelId);
        $oldMasksArray = $masksService->getList($oldMaskFilters);

        foreach ($oldMasksArray['data'] as $key => $value) {
            if (isset($value['blocks']) && is_array($value['blocks'])) {
                foreach ($value['blocks'] as $subkey => $someBlock) {
                    unset($oldMasksArray['data'][$key]['blocks'][$subkey]['id']);
                    unset($oldMasksArray['data'][$key]['blocks'][$subkey]['_id']);
                    $oldMasksArray['data'][$key]['blocks'][$subkey]['id'] = (string)new \MongoId();
                }
            }
            $oldIdArray[] = $value['id'];
            $theBigString = $theBigString . Json::encode($oldMasksArray['data'][$key]);
            $theBigString = $theBigString . "SMaskS";
        }
        $theBigString .= "SEntityS";
        $oldPagesArray = $pagesService->getList($oldMaskFilters);
        foreach ($oldPagesArray['data'] as $key => $value) {
            if (isset($value['blocks']) && is_array($value['blocks'])) {
                foreach ($value['blocks'] as $subkey => $someBlock) {
                    unset($oldPagesArray['data'][$key]['blocks'][$subkey]['id']);
                    unset($oldPagesArray['data'][$key]['blocks'][$subkey]['_id']);
                    $oldPagesArray['data'][$key]['blocks'][$subkey]['id'] = (string)new \MongoId();
                }
            }
            $oldIdArray[] = $value['id'];
            $theBigString = $theBigString . Json::encode($oldPagesArray['data'][$key]);
            $theBigString = $theBigString . "SPageS";
        }
        $newIdArray = array();

        //duplicate simple or manual queries and system contents

        $systemContentTypesFilter = Filter::Factory('Value')->setName('system')->setValue(true);
        $systemContentTypesLIst = Manager::getService('ContentTypes')->getList($systemContentTypesFilter);

        $systemTypesArray = array();
        foreach ($systemContentTypesLIst['data'] as $contentTypes) {
            $systemTypesArray[] = $contentTypes['id'];
        }

        $systemContentFilter = Filter::Factory('In')->setName('typeID')->setValue($systemTypesArray);
        $systemContentList = $contentsService->getList($systemContentFilter);

        $queriesFilter = Filter::Factory('In')->setName('type')->setValue(array("simple", "manual"));
        $queriesList = $queriesService->getList($queriesFilter);
        foreach ($queriesList['data'] as $someQuery) {
            if (strpos($theBigString, $someQuery['id'])) {
                $MongoId = new \MongoId();
                $MongoIdString = (string)$MongoId;
                $theBigString = str_replace($someQuery['id'], $MongoIdString, $theBigString);
                $someQuery['_id'] = $MongoId;
                unset($someQuery['id']);
                unset($someQuery['version']);
                $queriesService->create($someQuery);
            }
        }
        foreach ($systemContentList['data'] as $systemContent) {
            if (strpos($theBigString, $systemContent['id'])) {
                $MongoId = new \MongoId();
                $MongoIdString = (string)$MongoId;
                $theBigString = str_replace($systemContent['id'], $MongoIdString, $theBigString);
                $systemContent['_id'] = $MongoId;
                unset($systemContent['id']);
                unset($systemContent['version']);
                $contentsService->create($systemContent);
            }
        }


        foreach ($oldIdArray as $value) {
            $MongoId = new \MongoId();
            $MongoId = (string)$MongoId;
            $newIdArray[] = $MongoId;
            $theBigString = str_replace($value, $MongoId, $theBigString);
        }
        $explodedBigString = array();
        $explodedBigString = explode("SEntityS", $theBigString);

        $newSite = Json::decode($explodedBigString[0], Json::TYPE_ARRAY);
        $newMasksJsonArray = explode("SMaskS", $explodedBigString[1]);
        $newPagesJsonArray = explode("SPageS", $explodedBigString[2]);
        foreach ($insertData as $key => $value) {
            if (!empty($value)) {
                $newSite[$key] = $value;
            }
        }
        $newSite['_id'] = new \MongoId($newSite['id']);
        unset($newSite['id']);
        unset($newSite['version']);
        $returnArray = $this->_dataService->create($newSite);
        foreach ($newMasksJsonArray as $key => $value) {
            $newMask = Json::decode($newMasksJsonArray[$key], Json::TYPE_ARRAY);
            if (is_array($newMask)) {
                $newMask['_id'] = new \MongoId($newMask['id']);
                unset($newMask['id']);
                unset($newMask['version']);
                $masksService->create($newMask);
            }
        }
        foreach ($newPagesJsonArray as $key => $value) {
            $newPage = Json::decode($newPagesJsonArray[$key], Json::TYPE_ARRAY);
            if (is_array($newPage)) {
                $newPage['_id'] = new \MongoId($newPage['id']);
                unset($newPage['id']);
                unset($newPage['version']);
                $pagesService->create($newPage);
            }
        }

        return $returnArray;
    }

    public function createFromEmpty($insertData)
    {

        $this->translateService = Manager::getService('Translate');

        $this->locale = $insertData['defaultLanguage'];
        $insertData['nativeLanguage'] = $this->locale;

        if (is_array($insertData)) {
            $site = $this->_dataService->create($insertData);
        }

        if ($site['success'] === true) {
            // Make the mask skeleton
            $jsonMask = realpath(APPLICATION_PATH . "/data/default/site/mask.json");
            $maskObj = Json::decode(file_get_contents($jsonMask), Json::TYPE_ARRAY);
            $maskObj['site'] = $site['data']['id'];
            $maskObj['nativeLanguage'] = $this->locale;

            // Home mask
            $homeMaskCreation = $this->_createMask($maskObj, 'NewSite.homepage.title', 1);


            // Detail mask
            $detailSecondColumnId = (string)new \MongoId();
            $detailMaskCreation = $this->_createMask($maskObj, 'NewSite.single.title', 1, $detailSecondColumnId);

            // Search mask
            $searchColumnId = (string)new \MongoId();
            $searchMaskCreation = $this->_createMask($maskObj, 'NewSite.search.title', 1, $searchColumnId);


            if ($homeMaskCreation['success'] && $detailMaskCreation['success'] && $searchMaskCreation['success']) {
                /* Create Home Page */
                $jsonHomePage = realpath(APPLICATION_PATH . "/data/default/site/homePage.json");
                $itemJson = file_get_contents($jsonHomePage);
                $itemJson = preg_replace_callback('/###(.*)###/U', array(
                    $this,
                    '_replaceWithTranslation'
                ), $itemJson);
                $homePageObj = Json::decode($itemJson, Json::TYPE_ARRAY);
                $homePageObj['site'] = $site['data']['id'];
                $homePageObj['maskId'] = $homeMaskCreation['data']['id'];
                $homePageObj['nativeLanguage'] = $site['data']['nativeLanguage'];
                $homePageObj['i18n'] = array($site['data']['nativeLanguage'] => array(
                    "text" => $homePageObj['text'],
                    "title" => $homePageObj['title'],
                    "description" => $homePageObj['description']

                ));
                $homePage = Manager::getService('Pages')->create($homePageObj);

                /* Create Single Page */
                $jsonSinglePage = realpath(APPLICATION_PATH . "/data/default/site/singlePage.json");
                $itemJson = file_get_contents($jsonSinglePage);
                $itemJson = preg_replace_callback('/###(.*)###/U', array(
                    $this,
                    '_replaceWithTranslation'
                ), $itemJson);
                $singlePageObj = Json::decode($itemJson, Json::TYPE_ARRAY);
                $singlePageObj['site'] = $site['data']['id'];
                $singlePageObj['maskId'] = $detailMaskCreation['data']['id'];
                $singlePageObj['nativeLanguage'] = $site['data']['nativeLanguage'];
                $singlePageObj['i18n'] = array($site['data']['nativeLanguage'] => array(
                    "text" => $singlePageObj['text'],
                    "title" => $singlePageObj['title'],
                    "description" => $singlePageObj['description']

                ));
                $singlePageObj['blocks'][0]['id'] = (string)new \MongoId();
                $singlePageObj['blocks'][0]['parentCol'] = $detailSecondColumnId;
                $page = Manager::getService('Pages')->create($singlePageObj);

                /* Create Search Page */
                $jsonSearchPage = realpath(APPLICATION_PATH . "/data/default/site/searchPage.json");
                $itemJson = file_get_contents($jsonSearchPage);
                $itemJson = preg_replace_callback('/###(.*)###/U', array(
                    $this,
                    '_replaceWithTranslation'
                ), $itemJson);
                $searchPageObj = Json::decode($itemJson, Json::TYPE_ARRAY);
                $searchPageObj['nativeLanguage'] = $site['data']['nativeLanguage'];
                $searchPageObj['i18n'] = array($site['data']['nativeLanguage'] => array(
                    "text" => $searchPageObj['text'],
                    "title" => $searchPageObj['title'],
                    "description" => $searchPageObj['description']

                ));
                $searchPageObj['site'] = $site['data']['id'];
                $searchPageObj['maskId'] = $searchMaskCreation['data']['id'];
                $searchPageObj['blocks'][0]['id'] = (string)new \MongoId();
                $searchPageObj['blocks'][0]['parentCol'] = $searchColumnId;
                $searchPage = Manager::getService('Pages')->create($searchPageObj);

                if ($page['success'] && $homePage['success'] && $searchPage['success']) {

                    $updateMaskReturn = $this->_updateMenuForMask($homeMaskCreation['data'], $homePage['data']['id'], $searchPage['data']['id']);
                    $updateMaskReturn = $this->_updateMenuForMask($searchMaskCreation['data'], $homePage['data']['id'], $searchPage['data']['id']);
                    $updateMaskReturn = $this->_updateMenuForMask($detailMaskCreation['data'], $homePage['data']['id'], $searchPage['data']['id']);

                    //add 1 to 3 colmumns masks
                    for ($i = 1; $i <= 3; $i++) {
                        $mask = $this->_createMask($maskObj, 'NewSite.' . $i . 'col.title', $i);
                        $this->_updateMenuForMask($mask['data'], $homePage['data']['id'], $searchPage['data']['id']);
                    }

                    if ($updateMaskReturn['success'] === true) {
                        $updateData = $site['data'];
                        $updateData['homePage'] = $homePage['data']['id'];
                        $updateData['defaultSingle'] = $page['data']['id'];
                        $updateSiteReturn = $this->_dataService->update($updateData);
                        if ($updateSiteReturn['success'] === true) {
                            $returnArray = $updateSiteReturn;
                        } else {
                            $returnArray = array(
                                'success' => false,
                                "msg" => 'error during site update'
                            );
                        }
                    } else {
                        $returnArray = array(
                            'success' => false,
                            "msg" => 'error during mask update'
                        );
                    }
                } else {
                    $returnArray = array(
                        'success' => false,
                        "msg" => 'error during pages creation'
                    );
                }
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'error during masks creation'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'error during site creation'
            );
        }
        if (!$returnArray['success']) {
            $siteId = $site['data']['id'];
            $resultPages = Manager::getService('Pages')->deleteBySiteId($siteId);
            $resultMasks = Manager::getService('Masks')->deleteBySiteId($siteId);
            if ($resultPages['ok'] == 1 && $resultMasks['ok'] == 1) {
                $returnArray['delete'] = $this->_dataService->deleteById($siteId);
            } else {
                $returnArray['delete'] = array(
                    'success' => false,
                    "msg" => 'Error during the deletion of masks and pages'
                );
            }
        }

        return $returnArray;
    }

    /**
     * @param unknown $maskObj
     * @param unknown $name
     * @param number $numcol
     * @param string $forceCol
     * @return unknown
     */
    protected function _createMask($maskObj, $name, $numcol = 1, $forceCol = null)
    {
        // Search mask
        $mask = $maskObj;

        $searchFirstColumnId = (string)new \MongoId();
        $searchSecondColumnId = (string)new \MongoId();

        $mask['rows'][0]['id'] = (string)new \MongoId();
        $mask['rows'][1]['id'] = (string)new \MongoId();
        $mask['rows'][0]['columns'][0]['id'] = $searchFirstColumnId;

        $tempCol = $mask['rows'][1]['columns'][0];
        $tempCol['span'] = floor(12 / $numcol);
        unset($mask['rows'][1]['columns']);
        for ($i = 1; $i <= $numcol; $i++) {
            $mask['rows'][1]['columns'][$i - 1] = $tempCol;
            $mask['rows'][1]['columns'][$i - 1]['id'] = (string)new \MongoId();
            if ($forceCol && $i == 1) {
                $mask['rows'][1]['columns'][$i - 1]['id'] = $forceCol;
            }
            if ($i <= 2) {
                $mask['mainColumnId'] = $mask['rows'][1]['columns'][$i - 1]['id'];
            }
        }

        $mask['blocks'][0]['id'] = (string)new \MongoId();
        $mask['blocks'][0]['parentCol'] = $searchFirstColumnId;

        $mask['i18n'][$this->locale]['text'] = $mask['text'] = $this->translateService->getTranslation($name, $this->locale);
        $maskCreation = Manager::getService('Masks')->create($mask);
        if ($maskCreation['success']) {
            return $maskCreation;
        }
    }

    protected function _replaceWithTranslation($matches)
    {

        if ($matches[1] == 'Locale') {
            return $this->locale;
        }
        $result = $this->translateService->getTranslation($matches[1], $this->locale);
        if (empty($result)) {
            throw new \Rubedo\Exceptions\Server('can\'t translate :' . $matches[1]);
        }
        return $result;
    }


    protected function _updateMenuForMask($mask, $homePage, $searchPage)
    {
        $mask["blocks"][0]['configBloc'] = array(
            "useSearchEngine" => true,
            "rootPage" => $homePage,
            "searchPage" => $searchPage
        );
        $updateMaskReturn = Manager::getService('Masks')->update($mask);

        return $updateMaskReturn;
    }

    protected function _addReadableProperty($obj)
    {
        if (!self::isUserFilterDisabled()) {
            // Set the workspace for old items in database
            if (!isset($obj['workspace'])) {
                $obj['workspace'] = 'global';
            }

            $aclServive = Manager::getService('Acl');
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();

            if ((!in_array($obj['workspace'], $writeWorkspaces) && !in_array('all', $writeWorkspaces)) || !$aclServive->hasAccess("write.ui.dam")) {
                $obj['readOnly'] = true;
            } else {

                $obj['readOnly'] = false;
            }
        }

        return $obj;
    }

    protected function _isReadable($obj)
    {
        if (!self::isUserFilterDisabled()) {
            // Set the workspace for old items in database
            if (!isset($obj['workspace'])) {
                $obj['workspace'] = 'global';
            }
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();

            if (!in_array($obj['workspace'], $writeWorkspaces) && !in_array('all', $writeWorkspaces)) {
                return false;
            }
        }

        return true;
    }
}
