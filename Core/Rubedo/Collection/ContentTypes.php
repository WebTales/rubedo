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
use Rubedo\Interfaces\Collection\IContentTypes;

/**
 * Service to handle ContentTypes
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ContentTypes extends AbstractCollection implements IContentTypes
{

    public function __construct ()
    {
        $this->_collectionName = 'ContentTypes';
        parent::__construct();
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::create()
     */
    public function create (array $obj, $safe = true, $live = true)
    {
        $returnArray = parent::create($obj, $safe, $live);
        
        if ($returnArray["success"]) {
            $this->_indexContentType($returnArray['data']);
        }
        
        return $returnArray;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::update()
     */
    public function update (array $obj, $safe = true, $live = true)
    {
        $returnArray = parent::update($obj, $safe, $live);
        
        if ($returnArray["success"]) {
            $this->_indexContentType($returnArray['data']);
        }
        
        return $returnArray;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\AbstractCollection::destroy()
     */
    public function destroy (array $obj, $safe = true)
    {
        $returnArray = parent::destroy($obj, $safe);
        if ($returnArray["success"]) {
            $this->_indexContent($returnArray['data']);
        }
        return $returnArray;
    }

    /**
     * Push the content type to Elastic Search
     *
     * @param array $obj            
     */
    protected function _indexContentType ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService(
                'ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->indexContentType($obj['id'], $obj, TRUE);
    }

    /**
     * Remove the content type from Indexed Search
     *
     * @param array $obj            
     */
    protected function _unIndexContentType ($obj)
    {
        $ElasticDataIndexService = \Rubedo\Services\Manager::getService(
                'ElasticDataIndex');
        $ElasticDataIndexService->init();
        $ElasticDataIndexService->deleteContentType($obj['id'], TRUE);
    }
}
