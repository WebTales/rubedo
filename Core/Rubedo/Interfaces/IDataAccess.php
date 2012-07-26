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
 * @version $Id:
 */
namespace Rubedo\Interfaces;

/**
 * Interface of data access services
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo        
 */
interface IDataAccess
{

    /**
     * Initialize a data service handler to read or write in a DataBase
     * Collection
     *
     * @param string $collection
     *            name of the DB
     * @param string $dbName
     *            name of the DB
     * @param string $connection
     *            connection string to the DB server
     */
    public function __construct ($collection, $dbName = null, $connection = null);
}