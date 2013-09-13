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

use Rubedo\Interfaces\Security\IAcl;
use Rubedo\Services\Manager;
use Rubedo\Collection\AbstractCollection;
use Zend\Json\Json;

/**
 * Interface of Access Control List Implementation
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Acl implements IAcl
{

    /**
     * Path of the directory where role definition json are stored
     *
     * @var string
     */
    protected static $rolesDirectories;

    protected static $hasAccessRults = array();

    /**
     *
     * @return the $rolesDirectory
     */
    public static function getRolesDirectories ()
    {
        if (! isset(self::$rolesDirectories)) {
            self::lazyloadConfig();
        }
        return self::$rolesDirectories;
    }

    /**
     *
     * @param string $rolesDirectory            
     */
    public static function setRolesDirectories ($rolesDirectories)
    {
        self::$rolesDirectories = $rolesDirectories;
    }

    public function __construct ()
    {
        if (! isset(self::$rolesDirectories)) {
            self::lazyloadConfig();
        }
    }

    /**
     * Check if the current user has access to a given resource for a given
     * access mode
     *
     * @param string $resource
     *            resource name
     * @return boolean
     */
    public function hasAccess ($resource)
    {
        if (! isset(self::$hasAccessRults[$resource])) {
            $result = false;
            $currentUserService = Manager::getService('CurrentUser');
            $wasFiltered = AbstractCollection::disableUserFilter();
            $groups = $currentUserService->getGroups();
            AbstractCollection::disableUserFilter($wasFiltered);
            
            $roleArray = array();
            foreach ($groups as $group) {
                $roleArray = $this->addGroupToRoleArray($roleArray, $group);
            }
            $wasFiltered = AbstractCollection::disableUserFilter();
            $roleArray = $this->addGroupToRoleArray($roleArray, Manager::getService('Groups')->getPublicGroup());
            AbstractCollection::disableUserFilter($wasFiltered);
            
            foreach ($roleArray as $role) {
                if ($this->roleHasAccess($resource, $role)) {
                    $result = true || $result;
                    break;
                }
            }
            self::$hasAccessRults[$resource] = $result;
        }
        return self::$hasAccessRults[$resource];
    }

    /**
     * add role of the group to the current role Array
     *
     * @param array $roleArray            
     * @param array $group            
     * @return array
     */
    protected function addGroupToRoleArray (array $roleArray, array $group = null)
    {
        if (is_null($group)) {
            return $roleArray;
        }
        
        if (isset($group['roles']) && is_array($group['roles'])) {
            $groupRoleArray = array_values($group['roles']);
            $roleArray = array_merge($roleArray, $groupRoleArray);
        }
        return $roleArray;
    }

    /**
     * Check if a given role has access to the ressource
     *
     * @param string $resource            
     * @param string $role            
     * @return boolean
     */
    protected function roleHasAccess ($resource, $role)
    {
        // @todo temporary disabling workflow components
        if (strpos($resource, 'workflows') !== false) {
            return false;
        }
        
        // @todo temporary disabling nested contents
        if (strpos($resource, 'dependantTypes') !== false) {
            return false;
        }
        
        if (is_null($role)) {
            return false;
        }
        
        $rightsArray = $this->getRightsByRoleName($role);
        foreach ($rightsArray as $rightAccess) {
            $rightAccess = str_replace('.', '\.', $rightAccess);
            $rightAccess = str_replace('@dot', '.', $rightAccess);
            if (1 == preg_match('#' . $rightAccess . '#', $resource)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Return the role configuration from its name
     *
     * Read infos from configs/role/Jsonfile
     *
     * @param string $name            
     * @return array null
     */
    protected function getRoleByName ($name)
    {
        foreach (self::$rolesDirectories as $directory) {
            $pathName = $directory . '/' . $name . '.json';
            if (is_file($pathName)) {
                $roleInfos = Json::decode(file_get_contents($pathName), Json::TYPE_ARRAY);
                return $roleInfos;
            }
        }
        return null;
    }

    /**
     * Return the array of rights of a given role
     *
     * @param string $name            
     * @return array
     */
    protected function getRightsByRoleName ($name, $max = 5)
    {
        $rightsArray = array();
        $roleInfos = $this->getRoleByName($name);
        if ($roleInfos) {
            $rightsArray = $roleInfos['rights'];
            if (isset($roleInfos['includes']) && $max > 0) {
                foreach ($roleInfos['includes'] as $include) {
                    $rightsArray = array_merge($rightsArray, $this->getRightsByRoleName($include, $max - 1));
                }
            }
        }
        return $rightsArray;
    }

    /**
     * For a given list of ressource, build an array of authorized ressources
     *
     * @param array $ressourceArray
     *            array of ressources
     * @return array the array of boolean with ressource as key name
     */
    public function accessList (array $ressourceArray)
    {
        $aclArray = array();
        if (isset($this->_service)) {
            $object = $this->_service;
        } else {
            $object = $this;
        }
        foreach ($ressourceArray as $value) {
            $aclArray[$value] = $object->hasAccess($value);
        }
        return $aclArray;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Rubedo\Interfaces\Security\IAcl::getAvailaibleRoles()
     */
    public function getAvailaibleRoles ()
    {
        $userLang = 'en'; // default value
        $currentUserLanguage = Manager::getService('CurrentUser')->getLanguage();
        if (! empty($currentUserLanguage)) {
            $userLang = $currentUserLanguage;
        }
        $rolesInfosArray = array();
        
        foreach (self::$rolesDirectories as $directory) {
            $templateDirIterator = new \DirectoryIterator($directory);
            if (! $templateDirIterator) {
                throw new \Rubedo\Exceptions\Server('Can not instanciate iterator for role dir', "Exception67");
            }
            
            foreach ($templateDirIterator as $file) {
                if ($file->isDot() || $file->isDir()) {
                    continue;
                }
                if ($file->getExtension() == 'json') {
                    $roleJson = file_get_contents($file->getPathname());
                    $roleInfos = Json::decode($roleJson, Json::TYPE_ARRAY);
                    $roleLabel = $roleInfos['label'][$userLang];
                    $roleInfos['label'] = $roleLabel;
                    unset($roleInfos['rights']);
                    $rolesInfosArray[] = $roleInfos;
                }
            }
        }
        
        $response = array();
        $response['total'] = count($rolesInfosArray);
        $response['data'] = $rolesInfosArray;
        $response['success'] = TRUE;
        $response['message'] = 'OK';
        
        return $response;
    }

    /**
     * Read configuration from global application config and load it for the current class
     */
    public static function lazyloadConfig ()
    {
        $config = Manager::getService('config');
        self::setRolesDirectories($config['rolesDirectories']);
    }
    
    
}
