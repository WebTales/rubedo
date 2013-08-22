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
namespace Rubedo\Backoffice\Controller;

use WebTales\MongoFilters\Filter;
use Rubedo\Services\Manager;
use Rubedo\Collection\AbstractLocalizableCollection;

/**
 * Controller providing CRUD API for the Languages JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class LanguagesController extends DataAccessController
{

    public function __construct()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('Languages');
    }

    public function getBoLanguagesAction()
    {
        $directoryIterator = new \DirectoryIterator(APPLICATION_PATH . '/public/components/webtales/rubedo-localization');
        $boLangDirArray = array();
        foreach ($directoryIterator as $item) {
            if (! $item->isDir() || $item->isDot() || $item->getFilename() == '.git') {
                continue;
            }
            $boLangDirArray[] = $item->getFilename();
        }
        
        $boLangFilter = Filter::factory('In')->setName('locale')->setValue($boLangDirArray);
        
        $result = Manager::getService('Languages')->getList($boLangFilter, array(
            array(
                'property' => 'label',
                'direction' => 'ASC'
            )
        ));
        $languagesArray = array();
        foreach ($result['data'] as $languages) {
            $languagesArray[] = array(
                'key' => $languages['locale'],
                'label' => isset($languages['ownLabel']) && ($languages['ownLabel'] != "") ? $languages['ownLabel'] : $languages['label']
            );
        }
        return $this->_returnJson(array(
            'data' => $languagesArray,
            'success' => true
        ));
    }

    public function importLanguagesAction()
    {
        $tsvFile = APPLICATION_PATH . '/../data/ISO-639-2_utf-8.txt';
        $file = fopen($tsvFile, 'r');
        $service = Manager::getService('Languages');
        while ($line = fgetcsv($file, null, '|')) {
            if (empty($line[2])) {
                continue;
            }
            $lang = array();
            $lang['iso2'] = $line[2];
            $lang['locale'] = $line[2];
            $lang['iso3'] = $line[0];
            $lang['label'] = $line[3];
            $lang['labelFr'] = $line[4];
            
            $service->create($lang);
        }
        $this->_forward('index');
    }

    public function addLocalizationAction()
    {
        AbstractLocalizableCollection::localizeAllCollection();
        return $this->_returnJson(array(
            'success' => true
        ));
    }

    public function getFlagsListAction()
    {
        $directoryIterator = new \DirectoryIterator(APPLICATION_PATH . '/public/assets/flags/16');
        $flagsArray = array();
        foreach ($directoryIterator as $item) {
            if ($item->isDir() || $item->isDot()) {
                continue;
            }
            
            $matches = array();
            if (preg_match('#(.*)\.png#', $item->getFilename(), $matches)) {
                $flagsArray[] = array(
                    'code' => $matches[1]
                );
            }
        }
        $result = array(
            'data'=>$flagsArray);
        
        return $this->_returnJson($result);
    }
}