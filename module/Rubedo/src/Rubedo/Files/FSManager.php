<?php

namespace Rubedo\Files;

use League\Flysystem\GridFS\GridFSAdapter;
use League\Flysystem\Filesystem;
use Rubedo\Services\Manager;

class FSManager
{
    protected $_allowedDocumentMimeTypes = array(
        "application/pdf",
        "text/plain",
        // ms office < 2007
        "application/msword",
        "application/vnd.ms-powerpoint",
        "application/vnd.ms-excel",
        // ms office >= 2007
        "application/vnd.openxmlformats-officedocument.presentationml.presentation",
        "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
        "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
        // open office
        "application/vnd.oasis.opendocument.spreadsheet",
        "application/vnd.oasis.opendocument.formula",
        "application/vnd.oasis.opendocument.text",
        "application/vnd.oasis.opendocument.presentation"
    );
    protected $allowedResourceMimeTypes = array(
        "text/plain",
        "application/javascript",
        "text/css"
    );
    protected $_allowedIllustrationMimeTypes = array(
        "image/jpeg",
        "image/jpg",
        "image/png",
        "image/gif",
        "image/x-icon"
    );
    protected $_allowedVideoMimeTypes = array(
        "video/mp4",
        "video/mpeg",
        "video/x-flv",
        "video/webm",
        "application/ogg",
    );
    protected $_allowedAnimationMimeTypes = array(
        "application/x-shockwave-flash"
    );
    protected $_allowedSoundMimeTypes = array(
        "audio/mp3",
        "audio/mp4",
        "audio/mpeg",
        "audio/aac",
        'audio/ogg'
    );
    
    public function testTypeCompliance($mainFileType,$contentType){
        switch ($mainFileType) {
            case 'DocumentOrImage':
                if ((!in_array($contentType, $this->_allowedDocumentMimeTypes)) && (!in_array($contentType, $this->_allowedIllustrationMimeTypes))) {
                    return array(
                        'success' => false,
                        'msg' => 'Not authorized file extension ' . $contentType
                    );
                }
                break;
            case 'Resource':
                if (!in_array($contentType, $this->allowedResourceMimeTypes)) {
                    return array(
                        'success' => false,
                        'msg' => 'Not authorized file extension ' . $contentType
                    );
                }
                break;
            case 'Document':
                if (!in_array($contentType, $this->_allowedDocumentMimeTypes)) {
                    return array(
                        'success' => false,
                        'msg' => 'Not authorized file extension ' . $contentType
                    );
                }
                break;

            case 'Ilustration':
            case 'Image':
                if (!in_array($contentType, $this->_allowedIllustrationMimeTypes)) {
                    return array(
                        'success' => false,
                        'msg' => 'Not authorized file extension ' . $contentType
                    );
                }
                break;

            case 'Video':
                if (!in_array($contentType, $this->_allowedVideoMimeTypes)) {
                    return array(
                        'success' => false,
                        'msg' => 'Not authorized file extension ' . $contentType
                    );
                }
                break;

            case 'Animation':
                if (!in_array($contentType, $this->_allowedAnimationMimeTypes)) {
                    return array(
                        'success' => false,
                        'msg' => 'Not authorized file extension ' . $contentType
                    );
                }
                break;

            case 'Sound':
                if (!in_array($contentType, $this->_allowedSoundMimeTypes)) {
                    return array(
                        'success' => false,
                        'msg' => 'Not authorized file extension ' . $contentType
                    );
                }
                break;
            default:
                // throw new Rubedo\Exceptions\Server('no main type given');
                break;
        }
        return array(
            'success' => true,
        );
    }
    
    public function getFS($adapterConfig = null){
        $adapter= $this->getGridFSAdapter($adapterConfig);
        return (new Filesystem($adapter));
    }

    protected function getGridFSAdapter($adapterConfig = null){
        $mongoService=Manager::getService("MongoDataAccess");
        $mongoAdapter=$mongoService->getAdapter();
        $dbName=$mongoService->getDefaultDb();
        $gridFS=$mongoAdapter->$dbName->getGridFS();
        return(new GridFSAdapter($gridFS));
    }
}