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
 * Methods for update tool
 *
 * @author jbourdin
 *        
 */
class Update010000 extends Update
{

    protected static $toVersion = '1.1.0';

    /**
     * Add Default Id for default contents without this data
     *
     * @return boolean
     */
    public static function doUpdateTitleContents ()
    {
        $success = true;
        $contentPath = APPLICATION_PATH . '/data/default/';
        $contentIterator = new \DirectoryIterator($contentPath);
        foreach ($contentIterator as $directory) {
            if ($directory->isDot() || ! $directory->isDir()) {
                continue;
            }
            if (in_array($directory->getFilename(), array(
                'groups',
                'site'
            ))) {
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
                            case 'ContentTypes':
                            case 'DamTypes':
                                $property = 'type';
                                break;
                            case 'Groups':
                            case 'Themes':
                            case 'Wallpapers':
                                $property = 'name';
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

    public static function upgrade ()
    {
        // reset wallpapers and theme collections
        Manager::getService('Wallpapers')->drop();
        Manager::getService('Themes')->drop();
        
        // update default contents with their default Id
        static::doUpdateTitleContents();
        
        // reimport wallpapers and theme
        static::doInsertContents();
        
        // reset user prefs with theme
        static::resetUserTheme();
        
        return true;
    }

    /**
     * Set default theme for all Users
     * 
     * @return boolean
     */
    public static function resetUserTheme ()
    {
        $filter = Filter::factory('Value')->setName('isDefault')->setValue(true);
        $theme = Manager::getService('Themes')->findOne($filter);
        if ($theme) {
            $prefData = array();
            $prefData['iconSet'] = $theme['iconSet'];
            $prefData['stylesheet'] = $theme['stylesheet'];
            $prefData['themeColor'] = $theme['themeColor'];
            $prefData['wallpaper'] = $theme['wallpaper'];
            Manager::getService('PersonalPrefs')->customUpdate(array(
                '$set' => $prefData
            ), Filter::factory());
        }
        return true;
    }
}