<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Backoffice\Controller;

use Rubedo\Services\Manager;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;
use Mongo\DataAccess;

/**
 * Controller providing data restore from data dump zipfile
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 *
 */
class RestoreController extends DataAccessController
{

    /**
     * Temporary file path
     */
    protected $_zipFileName = '/Users/webtales/Demo/dump/ecommerce.zip';
    
    /**
     * Default restore mode : INSERT or UPSERT
     */
    protected $_restoreMode = "insert";    
       
    public function indexAction()
    {
    	$contentService = Manager::getService('MongoDataAccess');
    	$fileService = Manager::getService('MongoFileAccess');
    	$fileService->init();
    	$zipFileName = $this->_zipFileName;
    	$zip = zip_open($zipFileName);
    	if (is_resource($zip)) {
    		while ($zip_entry = zip_read($zip)) {
    			$fileName = zip_entry_name($zip_entry);
    			$fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    			$fileMimeType = mime_content_type($fileName);
    			switch ($fileExtension) {
    				case 'json': // collection
    					if (zip_entry_open($zip, $zip_entry, "r")) {
    						$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
    						$obj = json_decode($buf,TRUE);
    						if (is_array($obj)) {
    							$collectionName = array_keys($obj)[0];
    							$contentService->init($collectionName);
    							foreach ($obj[$collectionName]['data'] as $data) {
    								echo $data['id'];
    								if (\MongoId::isValid($data['id'])) {
    									$data['_id'] = new \MongoId($data['id']);
    									unset($data['id']);
    									switch ($this->_restoreMode) {
    										case 'insert':
    											try {
    												$contentService->insert($data);
    											} catch (\Exception $e) {
    												continue;
    											}
    											break;
    										case 'upsert':
    											try {
    												$contentService->insert($data, ['upsert'=>TRUE]);
    											} catch (\Exception $e) {
    												continue;
    											}
    											break;
    									}
    								}
    							}
    						}
    						zip_entry_close($zip_entry);
    					}
    					break;
    				default: // file
    					if (zip_entry_open($zip, $zip_entry, "r")) {
    						$buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
	    					$originalFileId = substr($fileName,0,24);
	    					$originalFileName = substr($fileName,25,strlen($fileName)-25);
	    					$obj = [
								'_id' => new \MongoId($originalFileId),
								'filename' => $originalFileName,
								'Content-Type' => $fileMimeType,
								'bytes' => $buf
	    					];
	    					$fileService->createBinary($obj);
    					}
    					break;
    			}
    		} 
    	} else {
    			echo "Erreur";
   		}
   	} 
}
