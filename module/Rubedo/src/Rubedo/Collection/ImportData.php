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
namespace Rubedo\Collection;

/**
 * Service to handle Raw Data Import
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 */
class ImportData extends AbstractCollection
{
	
	protected $_indexes = array(
			array(
					'keys' => array(
							'importKey' => 1
					)
			),
	);
	
    public function __construct()
    {
        $this->_collectionName = 'ImportData';
        parent::__construct();
    }
    
    
    /**
     * Write file to Import collection
     */
    public function writeImportFile($fileName, $separator, $userEncoding, $importKeyValue)
    {
    
    	// Read file to import
    	$receivedFile = fopen($fileName, 'r');
    
    	// Read the first line to start at the second line
    	fgetcsv($receivedFile, 1000000, $separator, '"', '\\');
    
    	$this->_dataService->emptyCollection();
    
    	$data = array();
    
    	while (($currentLine = fgetcsv($receivedFile, 1000000, $separator, '"', '\\')) !== false) {
    
    		// Encode fields
    		foreach ($currentLine as $key => $string) {
    			$utf8String = $this->forceUtf8($string, $userEncoding);
    			$currentLine['col' . $key] = $utf8String;
    			unset($currentLine[$key]);
    		}
    
    		// Add import unique key to handle multiple imports
    		$currentLine['importKey'] = $importKeyValue;
    
    		$data[] = $currentLine;
    
    	}
    
    	$this->_dataService->batchInsert($data, array());
    
    	fclose($receivedFile);
    
    	return true;
    }
    
    /**
     * Return the given string encoded in UTF-8
     *
     * @param string $string
     *            The string wich will be encoded
     * @param string $encoding
     *            The current encoding of the string
     * @return string Encoded string in UTF-8
     */
    protected function forceUtf8($string, $encoding)
    {
    	return mb_convert_encoding($string, "UTF-8", $encoding);
    }
}
