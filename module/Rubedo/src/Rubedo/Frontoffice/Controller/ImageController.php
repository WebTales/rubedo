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
namespace Rubedo\Frontoffice\Controller;

use Rubedo\Exceptions\Access;
use Rubedo\Exceptions\NotFound;
use Rubedo\Exceptions\User;
use Rubedo\Image\Image;
use Rubedo\Services\Manager;
use Zend\Http\Response\Stream;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Controller providing access to images in gridFS
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
class ImageController extends AbstractActionController
{

    protected function getTempImagesPaths()
    {
        return APPLICATION_PATH . '/cache/images';
    }

    public function generateDamAction()
    {
        // init parameters
        $now = Manager::getService('CurrentTime')->getCurrentTime();
        $mediaId = $this->params('mediaId');
        $damService = Manager::getService('Dam');
        $media = $damService->findById($mediaId);
        if (!$media) {
            throw new NotFound("No Image Found", "Exception8");
        }

        $fileId = $media['originalFileId'];

        $version = $this->params('version', 1);

        $width = $this->params('width', null);
        $width = ($width == 'x') ? null : $width;
        $height = $this->params('height', null);
        $height = ($height == 'x') ? null : $height;
        $mode = $this->params('mode', 'morph');

        // is the image public ?
        $isPublic = $damService->isPublic($mediaId);

        // get Image from GridFs
        $fileService = Manager::getService('Images');
        $obj = $fileService->findById($fileId);
        if (!$obj instanceof \MongoGridFSFile) {
            throw new NotFound("No Image Found", "Exception8");
        }

        $filePath = $this->getTempImagesPaths() . '/' . $fileId . '_' . $version;
        if (!is_file($filePath)) {
            $obj->write($filePath);
        }
        $meta = $obj->file;
        $filename = $meta['filename'];
        if ($filename != $this->params('filename')) {
            throw new NotFound("No Image Found", "Exception8");
        }

        $filename = isset($filename) ? $filename : basename($filePath);
        $nameSegment = explode('.', $filename);
        $extension = array_pop($nameSegment);
        if (!in_array(strtolower($extension), array(
            'gif',
            'jpg',
            'png',
            'jpeg'
        ))
        ) {
            throw new Access('Not authorized file extension.', "Exception21");
        }

        $type = strtolower($extension);
        $type = ($type == 'jpg') ? 'jpeg' : $type;

        // should we store generated image in public path of the website
        if ($isPublic) {
            $publicPath = APPLICATION_PATH . '/public/' . urldecode($this->getRequest()
                    ->getUri()
                    ->getPath());
            $publicDirName = dirname($publicPath);
            if (!file_exists($publicDirName)) {
                mkdir($publicDirName, 0755, true);
            }
            $tmpImagePath = $publicPath;
        } else {
            $fileSegment = isset($fileId) ? $fileId : crc32(dirname($filePath)) . '_' . basename($filePath); // str_replace('/', '_', $filePath);
            $tmpImagePath = $this->getTempImagesPaths() . '/' . $version . '/' . $fileSegment . '_' . (isset($width) ? $width : '') . '_' . (isset($height) ? $height : '') . '_' . (isset($mode) ? $mode : '') . '.' . $type;
        }
        if (!is_file($tmpImagePath) || $now - filemtime($tmpImagePath) > 7 * 24 * 3600) {
            if (!is_dir(dirname($tmpImagePath))) {
                mkdir(dirname($tmpImagePath), 0755, true);
            }
            $imageService = new Image();
            $newImage = $imageService->resizeImage($filePath, $mode, $width, $height, 'custom');

            switch ($type) {
                case 'jpeg':
                    imagejpeg($newImage, $tmpImagePath, 90);
                    break;
                case 'gif':
                    imagegif($newImage, $tmpImagePath);
                    break;
                case 'png':
                    imagepng($newImage, $tmpImagePath, 9, PNG_ALL_FILTERS);
                    break;
            }

            imagedestroy($newImage);
        }
        switch ($this->params()->fromQuery('attachment', null)) {
            case 'download':
                $forceDownload = true;
                break;
            default:
                $forceDownload = false;
                break;
        }

        $stream = fopen($tmpImagePath, 'r');

        $response = new Stream();
        $response->getHeaders()->addHeaders(array(
            'Content-type' => 'image/' . $type,
            'Content-Disposition' => 'inline; filename="' . $filename
        ));

        if ($isPublic) {
            $response->getHeaders()->addHeaders(array(
                'Pragma' => 'Public',
                'Cache-Control' => 'public, max-age=' . 7 * 24 * 3600,
                'Expires' => date(DATE_RFC822, strtotime("7 day"))
            ));
        }

        $response->setStream($stream);
        return $response;
    }

    function indexAction()
    {
        $now = Manager::getService('CurrentTime')->getCurrentTime();

        $fileId = $this->params()->fromQuery('file-id');
        $filePath = $this->params()->fromQuery('filepath');
        $size = $this->params()->fromQuery('size', 'custom');

        $version = $this->params()->fromQuery('version', 1);

        if ($size == 'thumbnail') {
            $width = 100;
            $height = 100;
            $mode = 'crop';
        } else {
            $width = $this->params()->fromQuery('width', null);
            $height = $this->params()->fromQuery('height', null);
            $mode = $this->params()->fromQuery('mode', 'morph');
        }
        Manager::getService("Logger")->debug($width);
        Manager::getService("Logger")->debug($size);
        Manager::getService("Logger")->debug($height);
        if (isset($fileId)) {
            $fileService = Manager::getService('Images');
            $obj = $fileService->findById($fileId);
            if (!$obj instanceof \MongoGridFSFile) {
                throw new NotFound("No Image Found", "Exception8");
            }

            $filePath = $this->getTempImagesPaths() . '/' . $fileId . '_' . $version;
            if (!is_file($filePath) || $now - filemtime($filePath) > 7 * 24 * 3600) {
                $obj->write($filePath);
            }
            $meta = $obj->file;
            $filename = $meta['filename'];
        }

        if ($filePath) {
            $filename = isset($filename) ? $filename : basename($filePath);
            $nameSegment = explode('.', $filename);
            $extension = array_pop($nameSegment);
            if (!in_array(strtolower($extension), array(
                'gif',
                'jpg',
                'png',
                'jpeg'
            ))
            ) {
                throw new Access('Not authorized file extension.', "Exception21");
            }

            $type = strtolower($extension);
            $type = ($type == 'jpg') ? 'jpeg' : $type;
            $fileSegment = isset($fileId) ? $fileId : crc32(dirname($filePath)) . '_' . basename($filePath); // str_replace('/', '_', $filePath);
            $tmpImagePath = $this->getTempImagesPaths() . '/' . $version . '/' . $fileSegment . '_' . (isset($width) ? $width : '') . '_' . (isset($height) ? $height : '') . '_' . (isset($mode) ? $mode : '') . '.' . $type;

            if (!is_file($tmpImagePath) || $now - filemtime($tmpImagePath) > 7 * 24 * 3600) {
                if (!is_dir(dirname($tmpImagePath))) {
                    mkdir(dirname($tmpImagePath), 0755, true);
                }
                $imageService = new Image();
                $newImage = $imageService->resizeImage($filePath, $mode, $width, $height, $size);

                switch ($type) {
                    case 'jpeg':
                        imagejpeg($newImage, $tmpImagePath, 90);
                        break;
                    case 'gif':
                        imagegif($newImage, $tmpImagePath);
                        break;
                    case 'png':
                        imagepng($newImage, $tmpImagePath, 9, PNG_ALL_FILTERS);
                        break;
                }

                imagedestroy($newImage);
            }
            switch ($this->params()->fromQuery('attachment', null)) {
                case 'download':
                    $forceDownload = true;
                    break;
                default:
                    $forceDownload = false;
                    break;
            }

            $stream = fopen($tmpImagePath, 'r');

            $response = new Stream();
            $response->getHeaders()->addHeaders(array(
                'Content-type' => 'image/' . $type,
                'Content-Disposition' => 'inline; filename="' . $filename,
                'Pragma' => 'Public',
                'Cache-Control' => 'public, max-age=' . 7 * 24 * 3600,
                'Expires' => date(DATE_RFC822, strtotime("7 day"))
            ));

            if ($forceDownload) {
                $response->getHeaders()->addHeaders(array(
                    'Content-Disposition' => 'attachment; filename="' . $filename
                ));
            } else {
                $response->getHeaders()->addHeaders(array(
                    'Content-Disposition' => 'inline; filename="' . $filename
                ));
            }
            $response->setStream($stream);
            return $response;
        } else {
            throw new User("No Image Given", "Exception80");
        }
    }

    public function getThumbnailAction()
    {
        $queryString = $this->getRequest()->getQuery();
        $queryString->set('size', 'thumbnail');
        return $this->forward()->dispatch('Rubedo\\Frontoffice\\Controller\\Image', array(
            'action' => 'index'
        ));
    }

    /**
     * Get avatar of the user
     *
     * @return mixed
     * @throws NotFound
     */
    public function getUserAvatarAction()
    {
        $userId = $this->params('userId');
        $version = $this->params('version');
        $width = $this->params('width', null);
        $height = $this->params('height', null);
        $mode = $this->params('mode', null);
        $user = Manager::getService('Users')->findById($userId);
        if (!$user || !isset($user['photo']) || empty($user['photo'])) {
            throw new NotFound("No Image Found", "Exception8");
        }
        $fileId = $user['photo'];

        $queryString = $this->getRequest()->getQuery();
        $queryString->set('file-id', $fileId);
        $queryString->set('version', $version);
        $queryString->set('width', $width);
        $queryString->set('height', $height);
        $queryString->set('mode', $mode);
        return $this->forward()->dispatch('Rubedo\\Frontoffice\\Controller\\Image', array(
            'action' => 'index'
        ));
    }
}
