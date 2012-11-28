<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IVersioning;

/**
 * Service to handle Versioning
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Versioning extends AbstractCollection implements IVersioning
{

    public function __construct() {
        $this->_collectionName = 'Versioning';
        parent::__construct();
    }
	
	public function addVersion($obj){
		$currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
		$currentTimeService = \Rubedo\Services\Manager::getService('CurrentTime');
		
		$createUser = null;
		$createTime = null;
		$version = null;
		
		$contentId = (string)$obj['_id'];
		
		$filter = array('contentId' => $contentId);
		$sort = array('publishVersion' => 'desc');
		
		$this->_dataService->addFilter($filter);
		$this->_dataService->addSort($sort);
		
		$contentVersions = $this->_dataService->read();
		
		if(isset($obj['createUser'])){
			$createUser = $obj['createUser'];
		}
		if(isset($obj['createTime'])){
			$createTime = $obj['createTime'];
		}
		if(isset($obj['version'])){
			$version = $obj['version'];
		}
		
		$version = array(
			'contentId' 			=> $contentId,
			'publishUser' 			=> $currentUserService->getCurrentUserSummary(),
			'publishTime'			=> $currentTime = $currentTimeService->getCurrentTime(),
			'contentCreateUser'		=> $createUser,
			'contentCreateTime'		=> $createTime,
			'contentVersion'		=> $version
		);
		
		if(count($contentVersions) > 0){
			$version['publishVersion'] = $contentVersions[0]['publishVersion'] + 1;
			
			$version = array_merge($version, $obj['live']);
		} else {
			$version['publishVersion'] = 0;
		}
		
		$returnArray = $this->_dataService->create($version);
		
		return $returnArray;
	}
}
