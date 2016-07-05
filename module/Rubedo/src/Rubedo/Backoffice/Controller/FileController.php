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

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Rubedo\Content\Context;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use Zend\Http\Request;

/**
 * Controller providing access control list
 *
 * Receveive Ajax Calls with needed ressources, send true or false for each of
 * them
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class FileController extends AbstractActionController
{
    protected function deletedFolderContent($path)
    {
        if (!is_dir($path)) {
            return;
        }
        $iterator = new \DirectoryIterator($path);
        foreach ($iterator as $item) {
            if ($item->isDot()) {
                continue;
            }
            if ($item->isDir()) {
                $this->deletedFolderContent($item->getPathname());
                if ($item->isWritable()) {
                    rmdir($item->getPathname());
                }
            } else {
                if ($item->isWritable()) {
                    unlink($item->getPathname());
                }
            }
        }
    }

    public function indexAction()
    {
        $fileService = Manager::getService('Files');
        $filesArray = $fileService->getList();
        $data = $filesArray['data'];
        $files = array();
        foreach ($data as $value) {
            $metaData = $value->file;
            $metaData['id'] = (string)$metaData['_id'];
            unset($metaData['_id']);
            $files[] = $metaData;
        }
        return new JsonModel(array(
            'data' => $files,
            'total' => $filesArray['count']
        ));
    }

    public function putAction()
    {
        Context::setExpectJson();
        $fileInfo = $this->params()->fromFiles('file');
        $finfo = new \finfo(FILEINFO_MIME);
        $mimeType = $finfo->file($fileInfo['tmp_name']);

            $fService=Manager::getService("FSManager");
            $complianceResult=$fService->testTypeCompliance($this->params()->fromPost('mainFileType', null),$mimeType);
            if(!$complianceResult["success"]){
                return $complianceResult;
            }
            $fs=$fService->getFS();
            $newPathId=(string) new \MongoId();
            $newPath=$newPathId.$fileInfo['name'];
            $stream = fopen($fileInfo['tmp_name'], 'r+');
            $result=$fs->writeStream($newPath,$stream,[
                'mimetype'=>$mimeType
            ]);
            if(!$result){
                throw new \Rubedo\Exceptions\Server('Unable to upload file');
            }
            $result=[
                "success"=>true,
                "data"=>[
                    "text"=>$fileInfo['name'],
                    "id"=>$newPath
                ]
            ];

//            $fileService = Manager::getService('Files');
//
//            $obj = array(
//                'serverFilename' => $fileInfo['tmp_name'],
//                'text' => $fileInfo['name'],
//                'filename' => $fileInfo['name'],
//                'Content-Type' => $mimeType,
//                'mainFileType' => $this->params()->fromPost('mainFileType', null)
//            );
//            $result = $fileService->create($obj);

        return new JsonModel($result);
    }

    public function updateAction()
    {
        Context::setExpectJson();
        $imageUrl = $this->params()->fromQuery("image");


        $info = pathinfo($imageUrl);

        $mimeType = "image/" . strtolower($info['extension']);

//        $fileService = Manager::getService('Files');
        $fService=Manager::getService("FSManager");
        $fs=$fService->getFS();
        $originalId = $this->params()->fromQuery("originalId",null);
        if (!$originalId||!$fs->has($originalId)) {
            throw new \Rubedo\Exceptions\NotFound("No Image Found", "Exception8");
        }
        $c = new Client();
        $request = new Request;
        $request->setUri($imageUrl);
        $result = $c->send($request);
        if (!$result) {
            throw new \Rubedo\Exceptions\Server("Unable to download new image");
        }
        $img = $result->getBody();
        $fs->update($originalId,$img,[
            'mimetype'=>$mimeType
        ]);
//        $fileService->destroy(array(
//            'id' => $originalId,
//            'version' => 1
//        ));
//        $fileObj = array(
//            'bytes' => $img,
//            'text' => $info['filename'],
//            'filename' => $info['basename'],
//            'Content-Type' => $mimeType,
//            'mainFileType' => 'Image',
//            '_id' => new \MongoId($originalId)
//        );
//
//        $fileService->createBinary($fileObj);

        // trigger deletion of cache : sys_get_temp_dir() . '/' . $fileId . '_'
        $paths = array(
            APPLICATION_PATH . '/public/generate-image',
            APPLICATION_PATH . '/cache/images',
        );
        $paths[]=sys_get_temp_dir();
        foreach ($paths as $path) {
            $directoryIterator = new \DirectoryIterator($path);
            foreach ($directoryIterator as $file) {
                if ($file->isDot()) {
                    continue;
                }
                if (strpos($file->getFilename(), $originalId) !== false) {
                    if ($file->isDir()) {
                        $this->deletedFolderContent($file->getPathname());
                    } else {
                        unlink($file->getPathname());
                    }
                }
            }
        }
        $associatedMedia=Manager::getService("Dam")->findByOriginalFileId($originalId);
        if($associatedMedia&&isset($associatedMedia["id"])){
            foreach ($paths as $path) {
                $directoryIterator = new \DirectoryIterator($path);
                foreach ($directoryIterator as $file) {
                    if ($file->isDot()) {
                        continue;
                    }
                    if (strpos($file->getFilename(), $associatedMedia["id"]) !== false) {
                        if ($file->isDir()) {
                            $this->deletedFolderContent($file->getPathname());
                        } else {
                            unlink($file->getPathname());
                        }
                    }

                }
            }
        }
        return $this->redirect()->toUrl('/backoffice/resources/afterPixlr.html');
    }

    public function deleteAction()
    {
        $fileId = $this->params()->fromPost('file-id');
        $version = $this->params()->fromPost('file-version', 1);

        if (isset($fileId)) {
            $fileService = Manager::getService('Files');
            $result = $fileService->destroy(array(
                'id' => $fileId,
                'version' => $version
            ));

            if ($result['success'] == true) {
                $this->redirect($this->_helper->url('index'));
            }
        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", 1);
        }
    }

    public function getAction()
    {
        return $this->forward()->dispatch('Rubedo\\Frontoffice\\Controller\\File', array(
            'action' => 'index'
        ));
    }

    public function getMetaAction()
    {
        $fileId = $this->params()->fromQuery('file-id');

        if (isset($fileId)) {
            $fileService = Manager::getService('Files');
            $obj = $fileService->findById($fileId);
            if (!$obj instanceof \MongoGridFSFile) {
                throw new \Rubedo\Exceptions\NotFound("No Image Found", "Exception8");
            }
            return new JsonModel($obj->file);
        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", "Exception7");
        }
    }

    public function dropAllFilesAction()
    {
        $fileService = Manager::getService('MongoFileAccess');
        $fileService->init();
        return new JsonModel($fileService->drop());
    }
}
