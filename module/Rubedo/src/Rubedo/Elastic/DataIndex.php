<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2016, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2016 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Elastic;

use Rubedo\Update\Install;

/**
 * Class implementing the Rubedo API to Elastic Search indexing services using
 * PHP elasticsearch API
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class DataIndex extends DataAbstract
{

    /**
     * Documents queue for indexing
     */
    protected $_documents;

    /**
     * Reindex all content or dam
     *
     * @param string $option
     *            : dam, content, user or all
     *
     * @return array
     */
    public function indexAll($option = 'all')
    {
        // for big data set
        set_time_limit(240);

        // Initialize variables
        $result = [];
        $suffix = time();
        $deleteContentsIndex = false;
        $deleteDamIndex = false;
        $deleteUsersIndex = false;
        $indexsToDel = [];

        // get dafault DB
        $dataAccess = $this->_getService('MongoDataAccess');
        $defaultDB = $dataAccess::getDefaultDb();
        $defaultDB = mb_convert_case($defaultDB, MB_CASE_LOWER, "UTF-8");

        // Retrieve config
        $installObject = new Install();
        $installObject->loadLocalConfig();
        $options = $installObject->getLocalConfig();

        // Create new index for contents, dam and users
        if ($option == 'all' or $option == 'content') {
        	$contentsIndexName = $this->getIndexNameFromConfig('contentIndex');
            if ($this->_client->indices()->exists(['index' => $contentsIndexName])) {
                $indexsToDel[] = $contentsIndexName;
                if(strpos($options["elastic"]["contentIndex"], "_") !== false) {
                    $contentsIndexName = substr($options["elastic"]["contentIndex"], 0, strpos($options["elastic"]["contentIndex"], "_"));
                } else {
                    $contentsIndexName = $options["elastic"]["contentIndex"];
                }
                $contentsIndexName .= "_".$suffix;
                $options["elastic"]["contentIndex"] = $contentsIndexName;
                $contentsIndexName = $defaultDB . "-" . $contentsIndexName;
            }

            // Retreive all content types
            $contentTypeList = $this->_getService('ContentTypes')->getList();

            foreach ($contentTypeList["data"] as $contentType) {
                // System contents are not indexed
                if (!isset($contentType['system']) or $contentType['system'] == FALSE) {
                    // Create content type mapping
                    $esContentsService = $this->_getService('ElasticContentTypes');
                    $esContentsService->init($contentsIndexName);
                    $esContentsService->setMapping($contentType["id"], $contentType);

                    // Reindex all contents from given type
                    $result = array_merge($result, $esContentsService->index($contentType["id"]));
                }
            }
        }

        if ($option == 'all' or $option == 'dam') {
        	$damIndexName = $this->getIndexNameFromConfig('damIndex');
            if ($this->_client->indices()->exists(['index' => $damIndexName])) {
                $indexsToDel[] = $damIndexName;
                if(strpos($options["elastic"]["damIndex"], "_") !== false) {
                    $damIndexName = substr($options["elastic"]["damIndex"], 0, strpos($options["elastic"]["damIndex"], "_"));
                } else {
                    $damIndexName = $options["elastic"]["damIndex"];
                }
                $damIndexName .= "_".$suffix;
                $options["elastic"]["damIndex"] = $damIndexName;
                $damIndexName = $defaultDB . "-" . $damIndexName;
            }

            // Retreive all dam types
            $damTypeList = $this->_getService('DamTypes')->getList();

            foreach ($damTypeList["data"] as $damType) {
                // Create dam type mapping
                $esDamService = $this->_getService('ElasticDamTypes');
                $esDamService->init($damIndexName);
                $esDamService->setMapping($damType["id"], $damType);

                // Reindex all assets from given type
                $result = array_merge($result, $esDamService->index($damType["id"]));
            }
        }

        if ($option == 'all' or $option == 'user') {
        	$usersIndexName = $this->getIndexNameFromConfig('userIndex');
            if ($this->_client->indices()->exists(['index' => $usersIndexName])) {
                $indexsToDel[] = $usersIndexName;
                if(strpos($options["elastic"]["userIndex"], "_") !== false) {
                    $usersIndexName = substr($options["elastic"]["userIndex"], 0, strpos($options["elastic"]["userIndex"], "_"));
                } else {
                    $usersIndexName = $options["elastic"]["userIndex"];
                }
                $usersIndexName .= "_".$suffix;
                $options["elastic"]["userIndex"] = $usersIndexName;
                $usersIndexName = $defaultDB . "-" . $usersIndexName;
            }

            // Retreive all user types
            $userTypeList = $this->_getService('UserTypes')->getList();

            foreach ($userTypeList["data"] as $userType) {
                // Create user type mapping with overwrite set to true
                $esUsersService = $this->_getService('ElasticUserTypes');
                $esUsersService->init($usersIndexName);
                $esUsersService->setMapping($userType["id"], $userType);

                // Reindex all assets from given type
                $result = array_merge($result, $esUsersService->index($userType["id"]));
            }
        }

        if(count($indexsToDel) > 0) {
            $installObject->saveLocalConfig($options, true);

            foreach($indexsToDel as $index) {
                $this->_client->indices()->delete(['index' => $index]);
            }
        }

        return ($result);
    }

    /**
     * Create or update index for existing content
     *
     * @param obj $data content data
     * @param boolean $bulk
     * @return array
     */
    public function indexContent($data, $bulk = false) {

    	$this->_getService('ElasticContents')->index($data, $bulk = false);

    }

    /**
     * Create or update index for existing dam
     *
     * @param obj $data dam data
     * @param boolean $bulk
     * @return array
     */
    public function indexDam($data, $bulk = false) {

    	$this->_getService('ElasticDam')->index($data, $bulk = false);

    }

    /**
     * Create or update index for existing user
     *
     * @param obj $data user data
     * @param boolean $bulk
     * @return array
     */
    public function indexUser($data, $bulk = false) {

    	$this->_getService('ElasticUsers')->index($data, $bulk = false);

    }

}
