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

use Rubedo\Interfaces\Collection\ISites, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

/**
 * Service to handle Sites
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Sites extends AbstractLocalizableCollection implements ISites
{
    protected static $nonLocalizableFields = array("text","alias","defaultLanguage","languages","activeMessagery","SMTPServer","SMTPPort","SMTPLogin","SMTPPassword","defaultEmail","accessibilityLevel","opquastLogin","opquastPassword","protocol","filter","theme","homePage","workspace","readOnly","defaultSingle","googleMapsKey","googleAnalyticsKey","disqusKey","builtOnEmptySite","builtOnModelSiteId","locStrategy","useBrowserLanguage");
    
    
    protected $_indexes = array(
        array(
            'keys' => array(
                'text' => 1
            ),
            'options' => array(
                'unique' => true
            )
        ),
        // array('keys'=>array('alias'=>1),'options'=>array('unique'=>true)),
        array(
            'keys' => array(
                'workspace' => 1
            )
        )
    );

    protected static $_overrideSiteName = array();

    protected static $_overrideSiteNameReverse = array();

    protected $_model = array(
        "text" => array(
            "domain" => "string",
            "required" => true
        ),
        "alias" => array(
            "domain" => "string",
            "required" => true
        ),
        "description" => array(
            "domain" => "string",
            "required" => true
        ),
        "keywords" => array(
            "domain" => "list",
            "required" => true,
            "items" => array(
                "domain" => "string",
                "required" => false
            )
        ),
        "mainLanguage" => array(
            "domain" => "string",
            "required" => true
        ),
		/*"languages" => array(
			"domain" => "list",
			"required" => true,
			"items" => array(
				"domain" => "string",
				"required" => false,
			),
		),*/
		"activeMessagery" => array(
            "domain" => "string",
            "required" => true
        ),
        "SMTPServer" => array(
            "domain" => "string",
            "required" => true
        ),
        "SMTPPort" => array(
            "domain" => "string",
            "required" => true
        ),
        "SMTPLogin" => array(
            "domain" => "string",
            "required" => true
        ),
        "SMTPPassword" => array(
            "domain" => "string",
            "required" => true
        ),
        "defaultEmail" => array(
            "domain" => "email",
            "required" => true
        ),
        "accessibilityLevel" => array(
            "domain" => "string",
            "required" => true
        ),
        "opquastLogin" => array(
            "domain" => "string",
            "required" => true
        ),
        "opquastPassword" => array(
            "domain" => "string",
            "required" => true
        ),
        "protocol" => array(
            "domain" => "string",
            "required" => true
        ),
		/*"filter" => array(
			"domain" => "array",
			"required" => true,
		),*/
		"theme" => array(
            "domain" => "string",
            "required" => true
        ),
        "homePage" => array(
            "domain" => "string",
            "required" => true
        ),
        "title" => array(
            "domain" => "string",
            "required" => true
        ),
        "author" => array(
            "domain" => "string",
            "required" => true
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
            $filter = Filter::factory('In')->setName('workspace')->setValue($readWorkspaceArray);
            $this->_dataService->addFilter($filter);
        }
    }

    public static function setOverride (array $array = null)
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

    public function __construct ()
    {
        $this->_collectionName = 'Sites';
        parent::__construct();
    }

    public function getHost ($site)
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

    public function findByHost ($host)
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

    public function deleteById ($id)
    {
        $mongoId = $this->_dataService->getId($id);
        return $this->_dataService->customDelete(array(
            '_id' => $mongoId
        ));
    }

    public function destroy (array $obj, $options = array())
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
    public function update (array $obj, $options = array())
    {
        $obj = $this->_initContent($obj);
        
        $return = parent::update($obj, $options);
        if ($return['success'] == true) {
            Manager::getService('Pages')->propagateWorkspace('root', $return['data']['workspace'], $return['data']['id']);
        }
        return $return;
    }

    protected function _setDefaultWorkspace ($site)
    {
        if (! isset($site['workspace']) || $site['workspace'] == '' || $site['workspace'] == array()) {
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
    public function create (array $obj, $options = array())
    {
        $obj = $this->_setDefaultWorkspace($obj);
        $obj = $this->_initContent($obj);
        
        return parent::create($obj, $options);
    }

    protected function _initContent ($obj)
    {
        // verify workspace can be attributed
        if (! self::isUserFilterDisabled()) {
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            if (! in_array($obj['workspace'], $writeWorkspaces)) {
                throw new \Rubedo\Exceptions\Access('You can not assign to this workspace', "Exception35");
            }
        }
        
        return $obj;
    }

    protected function _addReadableProperty ($obj)
    {
        if (! self::isUserFilterDisabled()) {
            // Set the workspace for old items in database
            if (! isset($obj['workspace'])) {
                $obj['workspace'] = 'global';
            }
            
            $aclServive = Manager::getService('Acl');
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            
            if ((! in_array($obj['workspace'], $writeWorkspaces) && ! in_array('all', $writeWorkspaces)) || ! $aclServive->hasAccess("write.ui.dam")) {
                $obj['readOnly'] = true;
            } else {
                
                $obj['readOnly'] = false;
            }
        }
        
        return $obj;
    }

    protected function _isReadable ($obj)
    {
        if (! self::isUserFilterDisabled()) {
            // Set the workspace for old items in database
            if (! isset($obj['workspace'])) {
                $obj['workspace'] = 'global';
            }
            $writeWorkspaces = Manager::getService('CurrentUser')->getWriteWorkspaces();
            
            if (! in_array($obj['workspace'], $writeWorkspaces) && ! in_array('all', $writeWorkspaces)) {
                return false;
            }
        }
        
        return true;
    }
}
