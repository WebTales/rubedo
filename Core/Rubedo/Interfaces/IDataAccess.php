<?php
namespace Rubedo\Interfaces;

/**
 * Interface of data access services
 * 
 * 
 * @author jbourdin
 *
 */
interface IDataAccess
{

    /**
     * Initialize a data service handler to read or write in a DB Collection
     * @param string $connection
     * @param string $db
     * @param string $collection
     */
    public function __construct($collection,$db = null,$mongo = null);
    

    
}

?>