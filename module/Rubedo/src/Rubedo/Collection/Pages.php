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

use Rubedo\Interfaces\Collection\IPages, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

/**
 * Service to handle Pages
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Pages extends AbstractCollection implements IPages
{

    protected $_indexes = array(
        array(
            'keys' => array(
                'site' => 1,
                'parentId' => 1,
                'orderValue' => 1
            )
        ),
        array(
            'keys' => array(
                'site' => 1,
                'parentId' => 1,
                'workspace' => 1,
                'orderValue' => 1
            )
        ),
        array(
            'keys' => array(
                'text' => 1,
                'parentId' => 1,
                'site' => 1
            ),
            'options' => array(
                'unique' => true
            )
        )
    );

    protected $_model = array(
        'text' => array(
            'domain' => 'string',
            'required' => true
        ),
        'maskId' => array(
            'domain' => 'string',
            'required' => true
        ),
        'site' => array(
            'domain' => 'string',
            'required' => true
        ),
        'blocks' => array(
            'domain' => 'list',
            'required' => true,
            'items' => array(
                'domain' => 'string',
                'required' => false
            )
        ),
        'title' => array(
            'domain' => 'string',
            'required' => true
        ),
        'description' => array(
            'domain' => 'string',
            'required' => true
        ),
		/*'keywords' => array(
			'domain' => 'list',
			'required' => true,
			'items' => array(
				'domain' => 'string',
				'required' => false,
			),
		),*/
		'pageURL' => array(
            'domain' => 'string',
            'required' => true
        ),
        'orderValue' => array(
            'domain' => 'integer',
            'required' => true
        ),
        'excludeFromMenu' => array(
            'domain' => 'bool',
            'required' => true
        ),
        'expandable' => array(
            'domain' => 'bool',
            'required' => true
        ),
        'workspace' => array(
            'domain' => 'string',
            'required' => true
        ),
        'inheritWorkspace' => array(
            'domain' => 'bool',
            'required' => true
        )
    );

    /**
     * Only access to content with read access
     *
     * @see \Rubedo\Collection\AbstractCollection::_init()
     */
    protected function _init ()
    {
        parent::_init();
        
        if (! self::isUserFilterDisabled()) {
            $readWorkspaceArray = Manager::getService('CurrentUser')->getReadWorkspaces();
            if (in_array('all', $readWorkspaceArray)) {
                return;
            }
            $filter = Filter::factory('In');
            $filter->setName('workspace')->setValue($readWorkspaceArray);
            $this->_dataService->addFilter($filter);
        }
    }

    public function __construct ()
    {
        $this->_collectionName = 'Pages';
        parent::__construct();
    }

    public function matchSegment ($urlSegment, $parentId, $siteId)
    {
        if (! $siteId) {
            return null;
        }
        $filters = Filter::factory('And');
        
        $filter = Filter::factory('Value');
        $filter->setName('pageURL')->setValue($urlSegment);
        $filters->addFilter($filter);
        
        $filter = Filter::factory('Value');
        $filter->setName('parentId')->setValue($parentId);
        $filters->addFilter($filter);
        
        $filter = Filter::factory('Value');
        $filter->setName('site')->setValue($siteId);
        $filters->addFilter($filter);
        
        return $this->_dataService->findOne($filters);
    }

    /**
     * Delete objects in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj
     *            data object
     * @param bool $options
     *            should we wait for a server response
     * @return array
     */
    public function destroy (array $obj, $options = array())
    {
        if ($this->hasDefaultPageAsChild($obj['id'])) {
            throw new \Rubedo\Exceptions\User("This page is the default single page or father of the default single page", "Exception47");
        }
        $deleteCond = Filter::factory('InUid')->setValue($this->_getChildToDelete($obj['id']));
        
        $resultArray = $this->_dataService->customDelete($deleteCond);
        
        if ($resultArray['ok'] == 1) {
            if ($resultArray['n'] > 0) {
                $returnArray = array(
                    'success' => true
                );
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'La suppression de la page a échoué'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => $resultArray["err"]
            );
        }
        
        $this->_clearCacheForPage($obj);
        return $returnArray;
    }

    /**
     * Check if page is or is the father of the default page of its site
     *
     * @return bool
     *
     */
    public function hasDefaultPageAsChild ($pageId)
    {
        $wasFiltered = AbstractCollection::disableUserFilter();
        $service = Manager::getService('Pages');
        $sitesService = Manager::getService('Sites');
        AbstractCollection::disableUserFilter($wasFiltered);
        
        // find site for $page ID
        $page = $service->findById($pageId);
        
        if ($page) {
            // find site
            $sitedId = $page['site'];
            $site = $sitesService->findById($sitedId);
            $defaultPage = $site['defaultSingle'];
            // find children
            $children = $service->_getChildToDelete($pageId);
            // do site default page match a child ?
            $response = in_array($defaultPage, $children);
        } else {
            $response = false;
        }
        
        return ($response);
    }

    /**
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $options = array())
    {
        $obj = $this->_initContent($obj);
        
        $returnValue = parent::update($obj, $options);
        
        $this->_clearCacheForPage($obj);
        
        $this->propagateWorkspace($obj['id'], $obj['workspace']);
        if ($returnValue['success']) {
            $returnValue['data'] = $this->addBlocks($returnValue['data']);
        }
        
        return $returnValue;
    }

    /**
     * Set workspace and URL.
     *
     * @param array $obj            
     * @throws \Exception
     * @return array
     */
    protected function _initContent ($obj)
    {
        
        // set inheritance for workspace
        if (! isset($obj['inheritWorkspace']) || $obj['inheritWorkspace'] !== false) {
            $obj['inheritWorkspace'] = true;
        }
        // resolve inheritance if not forced
        if ($obj['inheritWorkspace']) {
            unset($obj['workspace']);
            $ancestorsLine = array_reverse($this->getAncestors($obj));
            foreach ($ancestorsLine as $ancestor) {
                if (isset($ancestor['inheritWorkspace']) && $ancestor['inheritWorkspace'] == false) {
                    $obj['workspace'] = $ancestor['workspace'];
                    break;
                }
            }
            if (! isset($obj['workspace'])) {
                $site = Manager::getService('Sites')->findById($obj['site']);
                $obj['workspace'] = (isset($site['workspace']) && ! empty($site['workspace'])) ? $site['workspace'] : 'global';
            }
        }
        // verify workspace can be attributed
        if (! self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            
            if (! in_array($obj['workspace'], $writeWorkspaces)) {
                throw new \Rubedo\Exceptions\Access('You can not assign page to this workspace', "Exception48");
            }
        }
        
        // set text property
        if (empty($obj['text'])) {
            $obj['text'] = $obj['title'];
        }
        
        // set pageUrl
        if (empty($obj['pageURL'])) {
            $dataUrl = $obj['title'];
        } else {
            $dataUrl = $obj['pageURL'];
        }
        
        // filter URL
        $obj['pageURL'] = $this->filterUrl($dataUrl);
        if (isset($obj['id'])) {
            $obj = $this->writeBlocks($obj);
        }
        
        return $obj;
    }

    /**
     * Save the blocks of the given page
     *
     * Delete the no longer used blocks.
     *
     * @param array $obj            
     * @return array
     */
    protected function writeBlocks ($obj)
    {
        $blocksService = Manager::getService('Blocks');
        $arrayOfBlocksId = $blocksService->getIdListByPage($obj['id']);
        $blocks = isset($obj['blocks']) ? $obj['blocks'] : array();
        foreach ($blocks as $block) {
            $blocksService->upsertFromData($block, $obj['id'], 'page');
            if (isset($arrayOfBlocksId[$block['id']])) {
                unset($arrayOfBlocksId[$block['id']]);
            }
        }
        if (count($arrayOfBlocksId) > 0) {
            $blocksService->deletedByArrayOfId(array_keys($arrayOfBlocksId));
        }
        
        $obj['blocks'] = array();
        return $obj;
    }

    protected function _clearCacheForPage ($obj)
    {
        $pageId = $obj['id'];
        Manager::getService('UrlCache')->customDelete(Filter::factory('Value')->setName('pageId')
            ->setValue($pageId), array(
            'w' => false
        ));
    }

    public function findByNameAndSite ($name, $siteId)
    {
        $filters = Filter::factory()->addFilter(Filter::factory('Value')->setName('site')
            ->setValue($siteId))
            ->addFilter(Filter::factory('Value')->setName('text')
            ->setValue($name));
        return $this->_dataService->findOne($filters);
    }

    public function getListByMaskId ($maskId)
    {
        $filters = Filter::factory('Value')->setName('maskId')->setValue($maskId);
        return $this->getList($filters);
    }

    public function isMaskUsed ($maskId)
    {
        $filters = Filter::factory('Value')->setName('maskId')->setValue($maskId);
        $result = $this->_dataService->findOne($filters);
        return ($result != null) ? array(
            "used" => true
        ) : array(
            "used" => false
        );
    }

    public function create (array $obj, $options = array())
    {
        $obj = $this->_initContent($obj);
        $result = parent::create($obj, $options);
        $result['data'] = $this->addBlocks($result['data']);
        $newResult = $this->update($result['data']);
        return $newResult;
    }

    public function filterUrl ($url)
    {
        mb_regex_encoding('UTF-8');
        
        $normalizeChars = array(
            'Á' => 'A',
            'À' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Å' => 'A',
            'Ä' => 'A',
            'Æ' => 'AE',
            'Ç' => 'C',
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Í' => 'I',
            'Ì' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ð' => 'Eth',
            'Ñ' => 'N',
            'Ó' => 'O',
            'Ò' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ú' => 'U',
            'Ù' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            
            'á' => 'a',
            'à' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'å' => 'a',
            'ä' => 'a',
            'æ' => 'ae',
            'ç' => 'c',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'eth',
            'ñ' => 'n',
            'ó' => 'o',
            'ò' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            
            'ß' => 'sz',
            'þ' => 'thorn',
            'ÿ' => 'y',
            ' ' => '-',
            '\'' => '-'
        );
        
        $url = strtr(trim($url), $normalizeChars);
        $url = mb_strtolower($url, 'UTF-8');
        $url = mb_ereg_replace("[^A-Za-z0-9\\.\\-]", "", $url);
        $url = trim($url, '-');
        
        return $url;
    }

    public function deleteBySiteId ($id)
    {
        $wasFiltered = AbstractCollection::disableUserFilter();
        $filters = Filter::factory('Value')->setName('site')->setValue($id);
        $result = $this->_dataService->customDelete($filters);
        
        AbstractCollection::disableUserFilter($wasFiltered);
        
        return $result;
    }

    public function clearOrphanPages ()
    {
        $masksService = Manager::getService('Masks');
        
        $result = $masksService->getList();
        
        // recovers the list of contentTypes id
        foreach ($result['data'] as $value) {
            $masksArray[] = $value['id'];
        }
        
        $filters = Filter::factory('NotIn')->setName('maskId')->setValue($masksArray);
        
        $result = $this->customDelete($filters);
        
        if ($result['ok'] == 1) {
            return array(
                'success' => 'true'
            );
        } else {
            return array(
                'success' => 'false'
            );
        }
    }

    public function countOrphanPages ()
    {
        $masksService = Manager::getService('Masks');
        
        $result = $masksService->getList();
        
        // recovers the list of contentTypes id
        foreach ($result['data'] as $value) {
            $masksArray[] = $value['id'];
        }
        $filters = Filter::factory('NotIn')->setName('maskId')->setValue($masksArray);
        return $this->count($filters);
    }

    protected function _addReadableProperty ($obj)
    {
        $obj = $this->addBlocks($obj);
        if (! self::isUserFilterDisabled()) {
            // Set the workspace for old items in database
            if (! isset($obj['workspace'])) {
                $obj['workspace'] = 'global';
            }
            
            $aclServive = Manager::getService('Acl');
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            
            if (! in_array($obj['workspace'], $writeWorkspaces) || ! $aclServive->hasAccess("write.ui.pages")) {
                $obj['readOnly'] = true;
            } else {
                $obj['readOnly'] = false;
            }
        }
        
        return $obj;
    }

    /**
     * Add blocks from blocks collection to the given page
     *
     * @param array $obj            
     * @return array
     */
    protected function addBlocks ($obj)
    {
        $blocksTemp = array();
        $blocksService = Manager::getService('Blocks');
        $blockList = $blocksService->getListByPage($obj['id']);
        foreach ($blockList['data'] as $block) {
            $blocksTemp[] = $blocksService->getBlockData($block);
        }
        if (count($blocksTemp) > 0) {
            $obj['blocks'] = $blocksTemp;
        }
        return $obj;
    }

    public function propagateWorkspace ($parentId, $workspaceId, $siteId = null)
    {
        $filters = Filter::factory();
        if ($siteId) {
            $filters = Filter::factory('Value')->setName('site')->setValue($siteId);
        }
        $pageList = $this->readChild($parentId, $filters);
        foreach ($pageList as $page) {
            if (! self::isUserFilterDisabled()) {
                if (! $page['readOnly']) {
                    if ($page['workspace'] != $workspaceId) {
                        $this->update($page);
                    }
                }
            } else {
                if ($page['workspace'] != $workspaceId) {
                    $this->update($page);
                }
            }
        }
    }

    /**
     *
     * @param string $id
     *            id whose children should be deleted
     * @return array array list of items to delete
     */
    protected function _getChildToDelete ($id)
    {
        // delete at least the node
        $returnArray = array(
            $this->_dataService->getId($id)
        );
        
        // read children list
        $terms = $this->readChild($id);
        
        // for each child, get sublist of children
        if (is_array($terms)) {
            foreach ($terms as $value) {
                $returnArray = array_merge($returnArray, $this->_getChildToDelete($value['id']));
            }
        }
        
        return $returnArray;
    }
    
    /**
     * Return true if the given page is in the current rootline
     *
     * @param string $pageId
     *         id of the page
     * @return boolean
     */
    public function isInRootline($pageId) {
        //Get current page id
        $currentPage = Manager::getService('PageContent')->getCurrentPage();
        
        // If the current page is the given page we return true
        if($pageId == $currentPage){
            return true;
        }
        
        // Get the current page obj
        $currentPageObj = $this->findById($currentPage);
        $rootlineArray = array();
        
        //Get rootline of the current page
        $rootline = $this->getAncestors($currentPageObj);
        
        //Make the rootline pages id array
        foreach ($rootline as $ancestor) {
            $rootlineArray[] = $ancestor['id'];
        }
        
        //If the given page is in the rootline we return true
        if(in_array($pageId, $rootlineArray)) {
            return true;
        } else {
            return false;
        }
    }
}
