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
     
    public function indexAction()
    {
    	$dataAccessService = Manager::getService('MongoDataAccess');
    	$fileService = Manager::getService('MongoFileAccess');
    	$fileService->init();
    	$fileCollectionService = Manager::getService('Files');
    	$restoredElements = array();

        $request = $this->getRequest();

        if($request->isPost()) {
            $mimeTypes = finfo_open(FILEINFO_MIME_TYPE);
            $zipObj = new \ZipArchive();
            $restoreMode = $this->params()->fromPost('mode','INSERT');
            $fileUploadInfo = $request->getFiles("zipFile");
            $tmpFullPath = $fileUploadInfo["tmp_name"];
            $originalZipFileName = $fileUploadInfo["name"];
            $fileMimeType = $fileUploadInfo["type"];

            if($zipObj->open($tmpFullPath)) {
                $randomTmpFolder = hash("sha1", $tmpFullPath.$originalZipFileName.mt_rand());
                $extractTmpFolder = sys_get_temp_dir()."/".$randomTmpFolder;

                if($zipObj->extractTo($extractTmpFolder)) {
                    $extractedFiles = scandir($extractTmpFolder);

                    foreach($extractedFiles as $file) {
                        if($file !== "." && $file !== "..") {
                            $filePath = $extractTmpFolder."/".$file;
                            $fileExtension = finfo_file($mimeTypes, $filePath);

                            switch (pathinfo($filePath, PATHINFO_EXTENSION)) {
                                case 'json': // collection 
                                    $fileContent = file_get_contents($filePath);
                                    $obj = json_decode($fileContent,TRUE);
                                    if (is_array($obj)) {
                                        $collectionName = array_keys($obj)[0];
                                        $dataAccessService->init($collectionName);
                                        $restoredElements[$collectionName] = 0;
                                        foreach ($obj[$collectionName]['data'] as $data) {
                                            if (\MongoId::isValid($data['id'])) {
                                                $data['_id'] = new \MongoDB\BSON\ObjectId($data['id']);
                                                unset($data['id']);
                                                switch ($restoreMode) {
                                                    case 'INSERT':
                                                        try {
                                                            $dataAccessService->insert($data);
                                                            $restoredElements[$collectionName]++;
                                                        } catch (\Exception $e) {
                                                            continue;
                                                        }
                                                        break;
                                                    case 'UPSERT':
                                                        try {
                                                        	$data['id'] = (string) $data['_id'];
                                                        	unset($data['_id']);
                                                            $dataAccessService->update($data, ['upsert'=>TRUE]);
                                                            $restoredElements[$collectionName]++;
                                                        } catch (\Exception $e) {
                                                        	continue;
                                                        }
                                                        break;
                                                }
                                            }
                                        }
                                    }
                                    break;
                                default: // file
                                    $buf = file_get_contents($filePath);
                                    $originalFileId = substr($file,0,24);
                                    $originalFileName = substr($file,25,strlen($file)-25);
                                    $mainFileType = $fileCollectionService->getMainType($fileExtension);

                                    $fileObj = array(
                                    		'bytes' => $buf,
                                    		'text' => $originalFileName,
                                    		'filename' => $originalFileName,
                                    		'Content-Type' => $fileExtension,
                                    		'mainFileType' => $mainFileType,
                                    		'_id' => new \MongoDB\BSON\ObjectId($originalFileId)
                                    );
   
                                    switch ($restoreMode) {
                                    	case 'INSERT':
                                    		try {
                                    			$fileService->createBinary($fileObj);
                                    		} catch (\Exception $e) {
                                    			continue;
                                    		}
                                    		break;
                                    	case 'UPSERT':
                                    		try {
                                    			$fileService->destroy(array(
                                    				'id' => $originalFileId,
                                    				'version' => 1
                                    			));
                                    			$fileService->createBinary($fileObj);
                                    		} catch (\Exception $e) {
                                    			continue;
                                    		}
                                    		break;
                                    }
                                    break;
                            }
                        }
                    }
                }

                $zipObj->close();
            }
            
            $returnArray['success'] = true;
            $returnArray['message'] = "OK";
            $returnArray['restoredElements'] = $restoredElements;
            
        } else {
        	
        	$returnArray['success'] = FALSE;
        	$returnArray['message'] = "KO";
        	$returnArray['restoredElements'] = $restoredElements;
        }
        
        if (!$returnArray['success']) {
        	$this->getResponse()->setStatusCode(500);
        }
        return new JsonModel($returnArray);
   	} 
}
