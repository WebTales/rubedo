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

use Rubedo\Mongo\DataAccess, Rubedo\Collection\AbstractCollection, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

/**
 * Methods for update tool
 *
 * @author jbourdin
 *        
 */
class Update
{

    public static function doUpsertByTitleContents ()
    {
        $success = true;
        $contentPath = APPLICATION_PATH . '/../data/default/';
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
                    $item = \Zend_Json::decode($itemJson);
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
                        $filter = Filter::Factory('Value')->setName($property)->setValue($item[$property]);
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
    
    public static function truncateWallpapers ()
    {
        Manager::getService('Wallpapers')->drop();
        Manager::getService('Themes')->drop();
        \Rubedo\Update\Update::doUpsertByTitleContents();
    
        $filter = Filter::Factory('Value')->setName('isDefault')->setValue(true);
        $theme = Manager::getService('Themes')->findOne($filter);
        if ($theme) {
            $prefData = array();
            $prefData['iconSet'] = $theme['iconSet'];
            $prefData['stylesheet'] = $theme['stylesheet'];
            $prefData['themeColor'] = $theme['themeColor'];
            $prefData['wallpaper'] = $theme['wallpaper'];
            Manager::getService('PersonalPrefs')->customUpdate(array(
            '$set' => $prefData
            ), Filter::Factory());
        }
    }
}

?>