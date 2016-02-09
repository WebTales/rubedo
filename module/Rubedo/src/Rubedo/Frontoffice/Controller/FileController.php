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
namespace Rubedo\Frontoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;

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
class FileController extends AbstractActionController
{

	private $fileId;
	private $start  = -1;
	private $end    = -1;
	private $size   = 0;
	private $chunkSize;
	private $contentType;
	
    function indexAction()
    {
       	$this->fileId = $this->params()->fromQuery('file-id');
       	
        if (isset($this->fileId)) {

            $fileService = Manager::getService('Files');
            $obj = $fileService->findById($this->fileId);
            if (!$obj instanceof \MongoGridFSFile) {
                throw new \Rubedo\Exceptions\NotFound("No Image Found", "Exception8");
            }

	        $this->size = $obj->getSize();
	        $this->chunkSize = $obj->file["chunkSize"];
            $meta = $obj->file;
            $filename = $meta['filename'];
            $this->contentType = $meta['Content-Type'];
            $action = "inline";

            list ($subtype) = explode('/', $this->contentType);

            switch ($this->contentType) {
                case 'application/pdf':
                    $action = "inline";
                    break;
                default:
                    $action = "download";
                    break;
            }

            if ($subtype == 'image') {
                $action = "inline";
            }

            if ($subtype == 'text') {
                $action = "inline";
            }

            switch ($this->params()->fromQuery('attachment', null)) {
            	case 'download':
            		$action = "download";
            		break;
            	case 'inline':
            		$action = "inline";
            		break;
            	default:
            		break;
            }
            

            if ($subtype == 'video') {
            	$action = "stream";
            }
			
            switch ($action) {
            	case "download":
            		$response = new \Zend\Http\Response\Stream();
            		$response->getHeaders()->addHeaders(array(
            				'Content-Disposition' => 'attachment; filename="' . $filename
            		));
            		$response->getHeaders()->addHeaders(array(
            				'Content-Type' => $meta['Content-Type']
            		));
            		$stream = $obj->getResource();
            		$response->setStream($stream);
            		return $response;
            		break;
            	case "inline":
            		$response = new \Zend\Http\Response\Stream();
            		$response->getHeaders()->addHeaders(array(
            				'Content-Disposition' => 'inline; filename="' . $filename
            		));
            		$response->getHeaders()->addHeaders(array(
            				'Content-Type' => $meta['Content-Type']
            		));
            		$stream = $obj->getResource();
            		$response->setStream($stream);
            		return $response;
            		break;
            	case "stream":
            		$this->setHeader();
            		$this->stream();
            		exit;
            }

        } else {
            throw new \Rubedo\Exceptions\User("No Id Given", "Exception7");
        }
    }
    /**
     * Set proper header to stream files
     */
    private function setHeader()
    {
    	ob_get_clean();
    	header("Content-Type: ".$this->contentType);
    	header("Cache-Control: max-age=2592000, public");
    	header("Expires: ".gmdate('D, d M Y H:i:s', time()+2592000) . ' GMT');
    	$this->start = 0;
    	$this->end   = $this->size - 1;
    	header("Accept-Ranges: 0-".$this->end);
    	if (isset($_SERVER['HTTP_RANGE'])) {
    
    		$c_start = $this->start;
    		$c_end = $this->end;
    
    		list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
    		if (strpos($range, ',') !== false) {
    			header('HTTP/1.1 416 Requested Range Not Satisfiable');
    			header("Content-Range: bytes $this->start-$this->end/$this->size");
    			exit;
    		}
    		if ($range == '-') {
    			$c_start = $this->size - substr($range, 1);
    		}else{
    			$range = explode('-', $range);
    			$c_start = $range[0];
    			 
    			$c_end = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $c_end;
    		}
    		$c_end = ($c_end > $this->end) ? $this->end : $c_end;
    		if ($c_start > $c_end || $c_start > $this->size - 1 || $c_end >= $this->size) {
    			header('HTTP/1.1 416 Requested Range Not Satisfiable');
    			header("Content-Range: bytes $this->start-$this->end/$this->size");
    			exit;
    		}
    		$this->start = $c_start;
    		$this->end = $c_end;
    		$length = $this->end - $this->start + 1;
    		header('HTTP/1.1 206 Partial Content');
    		header("Content-Length: ".$length);
    		header("Content-Range: bytes $this->start-$this->end/".$this->size);
    	}
    	else
    	{
    		header("Content-Length: ".$this->size);
    	}
    	 
    }
    
    /**
     * perform the streaming of calculated range
     */
    private function stream() {
    	set_time_limit(0);
    	$firstChunk = intval($this->start/$this->chunkSize);
    	$lastChunk = intval($this->end/$this->chunkSize);
    	$filter = [
	    	'files_id' => new \MongoId($this->fileId),
	    	'n' => [
	    		'$gte' => $firstChunk,
	    		'$lte' => $lastChunk
	    	]
    	];
    	$mongoFilter=Filter::factory();
    	$mongoFilter->addFilter(Filter::factory("Value")->setName("files_id")->setValue(new \MongoId($this->fileId)));
    	$mongoFilter->addFilter(Filter::factory("OperatorToValue")->setName("n")->setOperator('$gte')->setValue($firstChunk));
    	$mongoFilter->addFilter(Filter::factory("OperatorToValue")->setName("n")->setOperator('$lte')->setValue($lastChunk));
    	$dataAccess = Manager::getService('MongoDataAccess');
    	$dataAccess->init('fs.chunks');
    	$cursor = $dataAccess->customFind($mongoFilter)->sort(['n' => 1]);
    	$i = $this->start;
    	foreach($cursor as $chunk) {
    		$borneInf = $chunk['n'] * $this->chunkSize;
    		$borneSup = $borneInf + $this->chunkSize - 1;
    		$startRead = ($this->start <= $borneInf) ? 0 : $this->start - $borneInf;
    		$bytesToRead = ($this->end <= $borneSup) ? $this->end - $borneInf + $startRead + 1 : $this->chunkSize - $startRead;
    		echo substr($chunk['data']->bin, $startRead, $bytesToRead);
    		flush();
    		$i += $bytesToRead;
    	}
    }    
    
    public function getThumbnailAction()
    {
        $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/attach_document.png');
        switch ($this->params()->fromQuery('file-type')) {
            case 'Audio':
                $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/speaker.png');
                break;
            case 'Video':
                $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/video.png');
                break;
            case 'Animation':
                $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/' . Manager::getService('Session')->get('iconSet', 'red') . '/128x128/palette.png');
                break;
            case 'Document':
                switch ($this->params()->fromQuery('content-type')) {
                    case 'application/msword':
                    case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                    case 'application/vnd.oasis.opendocument.text':
                        $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/generic/filetypes/word.png');
                        break;
                    case 'application/vnd.ms-excel':
                    case 'text/csv':
                    case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                    case 'application/vnd.oasis.opendocument.spreadsheet':
                        $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/generic/filetypes/excel.png');
                        break;
                    case 'application/vnd.ms-powerpoint':
                    case 'application/vnd.openxmlformats-officedocument.presentationml.presentation':
                    case 'application/vnd.oasis.opendocument.presentation':
                        $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/generic/filetypes/powerpoint.png');
                        break;
                    case 'application/pdf':
                        $iconPath = realpath(APPLICATION_PATH . '/public/components/webtales/rubedo-backoffice-ui/www/resources/icones/generic/filetypes/pdf.png');
                        break;
                    default:
                        break;
                }
                break;
            default:
                break;
        }

        $queryString = $this->getRequest()->getQuery();
        $queryString->set('size', 'thumbnail');
        $queryString->set('file-id', null);
        $queryString->set('filepath', $iconPath);
        return $this->forward()->dispatch('Rubedo\\Frontoffice\\Controller\\Image', array(
            'action' => 'index'
        ));
    }
}
