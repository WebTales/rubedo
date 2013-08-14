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
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IFiles;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;

/**
 * Service to handle Users
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Files extends AbstractFileCollection implements IFiles {
	protected $_allowedDocumentMimeTypes = array (
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
	protected $_allowedIllustrationMimeTypes = array (
			"image/jpeg",
			"image/jpg",
			"image/png",
			"image/gif",
			"image/x-icon" 
	);
	protected $_allowedVideoMimeTypes = array (
			"video/mp4",
			"video/mpeg",
			"video/x-flv" 
	);
	protected $_allowedAnimationMimeTypes = array (
			"application/x-shockwave-flash" 
	);
	protected $_allowedSoundMimeTypes = array (
			"audio/mp3",
			"audio/mp4",
			"audio/mpeg",
			"audio/aac",
			'audio/ogg' 
	);
	public function create(array $fileObj) {
		switch ($fileObj ['mainFileType']) {
			case 'Document' :
				if (! in_array ( $fileObj ['Content-Type'], $this->_allowedDocumentMimeTypes )) {
					return array (
							'success' => false,
							'msg' => 'Not authorized file extension ' . $fileObj ['Content-Type'] 
					);
				}
				break;
			
			case 'Ilustration' :
				if (! in_array ( $fileObj ['Content-Type'], $this->_allowedIllustrationMimeTypes )) {
					return array (
							'success' => false,
							'msg' => 'Not authorized file extension ' . $fileObj ['Content-Type'] 
					);
				}
				break;
			
			case 'Video' :
				if (! in_array ( $fileObj ['Content-Type'], $this->_allowedVideoMimeTypes )) {
					return array (
							'success' => false,
							'msg' => 'Not authorized file extension ' . $fileObj ['Content-Type'] 
					);
				}
				break;
			
			case 'Animation' :
				if (! in_array ( $fileObj ['Content-Type'], $this->_allowedAnimationMimeTypes )) {
					return array (
							'success' => false,
							'msg' => 'Not authorized file extension ' . $fileObj ['Content-Type'] 
					);
				}
				break;
			
			case 'Sound' :
				if (! in_array ( $fileObj ['Content-Type'], $this->_allowedSoundMimeTypes )) {
					return array (
							'success' => false,
							'msg' => 'Not authorized file extension ' . $fileObj ['Content-Type'] 
					);
				}
				break;
			default :
				// throw new Rubedo\Exceptions\Server('no main type given');
				break;
		}
		
		$result = parent::create ( $fileObj );
		
		if ($result ['success']) {
			$wasFiltered = AbstractCollection::disableUserFilter ();
			Manager::getService ( 'Dam' )->updateVersionForFileId ( $result ['data'] ['id'] );
			AbstractCollection::disableUserFilter ( $wasFiltered );
		}
		
		return $result;
	}
	public function createBinary(array $fileObj, $options = array()) {
		switch ($fileObj ['mainFileType']) {
			case 'Document' :
				if (! in_array ( $fileObj ['Content-Type'], $this->_allowedDocumentMimeTypes )) {
					return array (
							'success' => false,
							'msg' => 'Not authorized file extension ' . $fileObj ['Content-Type'] 
					);
				}
				break;
			
			case 'Ilustration' :
				if (! in_array ( $fileObj ['Content-Type'], $this->_allowedIllustrationMimeTypes )) {
					return array (
							'success' => false,
							'msg' => 'Not authorized file extension ' . $fileObj ['Content-Type'] 
					);
				}
				break;
			
			case 'Video' :
				if (! in_array ( $fileObj ['Content-Type'], $this->_allowedVideoMimeTypes )) {
					return array (
							'success' => false,
							'msg' => 'Not authorized file extension ' . $fileObj ['Content-Type'] 
					);
				}
				break;
			
			case 'Animation' :
				if (! in_array ( $fileObj ['Content-Type'], $this->_allowedAnimationMimeTypes )) {
					return array (
							'success' => false,
							'msg' => 'Not authorized file extension ' . $fileObj ['Content-Type'] 
					);
				}
				break;
			
			case 'Sound' :
				if (! in_array ( $fileObj ['Content-Type'], $this->_allowedSoundMimeTypes )) {
					return array (
							'success' => false,
							'msg' => 'Not authorized file extension ' . $fileObj ['Content-Type'] 
					);
				}
				break;
			default :
				// throw new Rubedo\Exceptions\Server('no main type given');
				break;
		}
		
		$result = parent::createBinary ( $fileObj );
		
		if ($result ['success']) {
			$wasFiltered = AbstractCollection::disableUserFilter ();
			Manager::getService ( 'Dam' )->updateVersionForFileId ( $result ['data'] ['id'] );
			AbstractCollection::disableUserFilter ( $wasFiltered );
		}
		
		return $result;
	}
	
	public function  findByFileNAme($name) {
	
	    $filter = Filter::factory('Value')->SetName('filename')->setValue($name);
	    return $this->findOne($filter);
	
	}
}
