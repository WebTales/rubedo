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
namespace Rubedo\Update;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Json\Json;
/**
 * Methods
 * for
 * update
 * tool
 *
 * @author adobre
 *
 */
class Update020000 extends Update
{

    protected static $toVersion = '2.1.0';


    public static function doCreateDefaultUserTypes ()
    {
        $success = true;
        $contentPath = APPLICATION_PATH . '/data/default/';
        $contentIterator = new \DirectoryIterator($contentPath);
        foreach ($contentIterator as $directory) {
            if ($directory->isDot() || ! $directory->isDir()) {
                continue;
            }
            if ($directory->getFilename()!="UserTypes"){
                continue;
            }
            $collection = ucfirst($directory->getFilename());
            $itemsJson = new \DirectoryIterator($contentPath . '/' . $directory->getFilename());
            foreach ($itemsJson as $file) {
                if ($file->isDot() || $file->isDir()) {
                    continue;
                }
                if ($file->getExtension() == 'json') {
                    $itemJson = file_get_contents($file->getPathname());
                    $item = Json::decode($itemJson,Json::TYPE_ARRAY);
                    try {
                        switch ($collection) {
                            case 'UserTypes':
                                $property = 'type';
                                break;
                            default:
                                $property = 'text';
                                break;
                        }
                        $filter = Filter::factory('Value')->setName($property)->setValue($item[$property]);
                        $result = Manager::getService($collection)->customUpdate(array(
                            '$set' => array(
                                'defaultId' => $item['defaultId']
                            )
                        ), $filter);
                    } catch (\Rubedo\Exceptions\User $exception) {
                        $result['success'] = true;
                    }

                    $success = $result['success'] && $success;
                }
            }
        }

        return $success;
    }

    public static function doUpdateUserTypes ()
    {

        $success = true;
        $publicGroup = Manager::getService('Groups')->findByName('public');
        $userTypes=Manager::getService('UserTypes')->getList();
        foreach ($userTypes['data'] as $userType){
            $userType['defaultGroup']=$publicGroup['id'];
            $result=Manager::getService('UserTypes')->update($userType);
            $success = $result['success'] && $success;
        }
        return $success;
    }


    public static function doUpdateUsers ()
    {
        $success = true;
        $publicGroup=Manager::getService("Groups")->findByName("public");

        $filters=Filter::factory();
        $filters->addFilter(Filter::factory('Value')->setName('UTType')
            ->setValue("default"));
        $defaultUserType=Manager::getService("UserTypes")->findOne($filters);

        $filters2=Filter::factory();
        $filters2->addFilter(Filter::factory('Value')->setName('UTType')
            ->setValue("email"));
        $emailUserType=Manager::getService("UserTypes")->findOne($filters2);

        $usersService=Manager::getService("Users");
        $users=$usersService->getList();
        foreach ($users['data'] as $user){
            if (!isset($user['typeId'])){
                if ((isset($user['groups']))&&(is_array($user['groups']))&&($user['groups']!=array($publicGroup['id']))){
                    $user['typeId']=$defaultUserType['id'];
                    if (!isset($user['fields'])){
                        $user['fields']=array();
                        //process proper fields array creation here
                    }

                } else {
                    $user['typeId']=$emailUserType['id'];
                    if (!isset($user['name'])){
                        $user['name']=$user['email'];
                    }
                    if (!isset($user['login'])){
                        $user['login']=$user['email'];
                    }
                    if (!isset($user['fields'])){
                        $user['fields']=array();
                    }
                }


                if (!isset($user['status'])){
                    $user['status']="approved";
                }
                if (!isset($user['taxonomy'])){
                    $user['taxonomy']=array();
                }
                $updateResult=$usersService->update($user);
                $success=$updateResult['success'] && $success;
            }
        }
        return $success;
    }
    /**
     * do
     * the
     * upgrade
     *
     * @return boolean
     */
    public static function upgrade ()
    {

        // create default user types
        static::doCreateDefaultUserTypes();

        // update user types with default group info
        static::doUpdateUserTypes();

        // update users
        static::doUpdateUsers();

        return true;
    }


}